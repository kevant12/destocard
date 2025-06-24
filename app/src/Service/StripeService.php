<?php

namespace App\Service;

use Stripe\StripeClient;

class StripeService
{
    private StripeClient $client;

    public function __construct(string $stripeSecretKey)
    {
        $this->client = new StripeClient($stripeSecretKey);
    }

    public function getClient(): StripeClient
    {
        return $this->client;
    }

    // Exemple : créer un paiement
    public function createPaymentIntent(array $params)
    {
        return $this->client->paymentIntents->create($params);
    }

    // Ajoute ici d'autres méthodes utiles selon tes besoins
} 