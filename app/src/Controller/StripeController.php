<?php

namespace App\Controller;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/api/create-payment-intent', name: 'api_create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(Request $request, StripeService $stripeService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $amount = isset($data['amount']) ? (int)$data['amount'] : 0;

        if ($amount <= 0) {
            return $this->json(['error' => 'Montant invalide'], 400);
        }

        try {
            $paymentIntent = $stripeService->createPaymentIntent([
                'amount' => $amount, // en centimes
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return $this->json(['clientSecret' => $paymentIntent->client_secret]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/create-customer', name: 'api_create_stripe_customer', methods: ['POST'])]
    public function createCustomer(Request $request, StripeService $stripeService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return $this->json(['error' => 'Email requis'], 400);
        }

        try {
            $customer = $stripeService->getClient()->customers->create([
                'email' => $email,
            ]);
            return $this->json(['customerId' => $customer->id]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/create-subscription', name: 'api_create_stripe_subscription', methods: ['POST'])]
    public function createSubscription(Request $request, StripeService $stripeService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $customerId = $data['customerId'] ?? null;
        $priceId = $data['priceId'] ?? null;

        if (!$customerId || !$priceId) {
            return $this->json(['error' => 'customerId et priceId requis'], 400);
        }

        try {
            $subscription = $stripeService->getClient()->subscriptions->create([
                'customer' => $customerId,
                'items' => [
                    ['price' => $priceId],
                ],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
            ]);
            return $this->json([
                'subscriptionId' => $subscription->id,
                'clientSecret' => $subscription->latest_invoice->payment_intent->client_secret ?? null,
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/payment-intent/{id}', name: 'api_get_payment_intent', methods: ['GET'])]
    public function getPaymentIntent(string $id, StripeService $stripeService): JsonResponse
    {
        try {
            $paymentIntent = $stripeService->getClient()->paymentIntents->retrieve($id, []);
            return $this->json($paymentIntent);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    #[Route('/api/stripe/webhook', name: 'api_stripe_webhook', methods: ['POST'])]
    public function webhook(Request $request, StripeService $stripeService): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');
        $endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? null;

        if (!$endpointSecret) {
            return $this->json(['error' => 'Webhook secret non configuré'], 500);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );

            // Exemple de gestion d'événement Stripe
            if ($event->type === 'payment_intent.succeeded') {
                $paymentIntent = $event->data->object;
                // ... traitement métier
            }

            return $this->json(['status' => 'success']);
        } catch (\UnexpectedValueException $e) {
            return $this->json(['error' => 'Payload invalide'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return $this->json(['error' => 'Signature invalide'], 400);
        }
    }
} 