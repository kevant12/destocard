<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    #[Route('/add-to-cart/{id}', name: 'add_to_cart', methods: ['POST'])]
    public function addToCart(int $id, Request $request): Response
    {
        try {
            $result = $this->cartService->addToCart($id);

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'cartCount' => $result['cartCount'],
                    'total' => $result['total']
                ]);
            }

            return $this->redirectToRoute('cart');
        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }

            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('cart');
        }
    }

    #[Route('/cart', name: 'cart', methods: ['GET'])]
    public function cart(): Response
    {
        // Valider le stock avant d'afficher le panier
        $validation = $this->cartService->validateStock();
        
        if (!empty($validation['errors'])) {
            foreach ($validation['errors'] as $error) {
                $this->addFlash('warning', $error);
            }
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $validation['cart'],
            'cartCount' => $this->cartService->getCartCount(),
            'total' => $this->cartService->calculateTotal()
        ]);
    }

    #[Route('/cart/update/{id}', name: 'cart_update', methods: ['POST'])]
    public function updateQuantity(int $id, Request $request): Response
    {
        try {
            $quantity = (int) $request->request->get('quantity', 1);
            $result = $this->cartService->updateQuantity($id, $quantity);

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'cartCount' => $result['cartCount'],
                    'total' => $result['total'],
                    'itemTotal' => $result['itemTotal']
                ]);
            }

            return $this->redirectToRoute('cart');
        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }

            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('cart');
        }
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove', methods: ['POST'])]
    public function removeItem(int $id, Request $request): Response
    {
        $result = $this->cartService->removeFromCart($id);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'cartCount' => $result['cartCount'],
                'total' => $result['total']
            ]);
        }

        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/buy', name: 'cart_buy', methods: ['POST'])]
    public function buy(): Response
    {
        // Valider le stock avant de finaliser l'achat
        $validation = $this->cartService->validateStock();
        
        if (!$validation['valid']) {
            foreach ($validation['errors'] as $error) {
                $this->addFlash('error', $error);
            }
            return $this->redirectToRoute('cart');
        }

        // TODO: Implémenter la logique d'achat
        // - Créer une commande
        // - Mettre à jour les stocks
        // - Enregistrer les détails de la commande
        
        $this->cartService->clearCart();
        $this->addFlash('success', 'Achat effectué avec succès !');
        return $this->redirectToRoute('cart');
    }
} 