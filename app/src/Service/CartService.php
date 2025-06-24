<?php

namespace App\Service;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private $session;
    private $productsRepository;

    public function __construct(
        RequestStack $requestStack,
        ProductsRepository $productsRepository
    ) {
        $this->session = $requestStack->getSession();
        $this->productsRepository = $productsRepository;
    }

    public function getCart(): array
    {
        return $this->session->get('cart', []);
    }

    public function addToCart(int $productId): array
    {
        $cart = $this->getCart();
        $product = $this->productsRepository->find($productId);

        if (!$product) {
            throw new \Exception('Produit non trouvé');
        }

        if (isset($cart[$productId])) {
            if ($cart[$productId]['quantity'] < $product->getQuantity()) {
                $cart[$productId]['quantity']++;
            }
        } else {
            if ($product->getQuantity() > 0) {
                $cart[$productId] = [
                    'id' => $product->getId(),
                    'name' => $product->getTitle(),
                    'price' => $product->getPrice(),
                    'quantity' => 1,
                    'stock' => $product->getQuantity()
                ];
            }
        }

        $this->session->set('cart', $cart);

        return [
            'cart' => $cart,
            'total' => $this->calculateTotal(),
            'cartCount' => $this->getCartCount()
        ];
    }

    public function updateQuantity(int $productId, int $quantity): array
    {
        $cart = $this->getCart();
        $product = $this->productsRepository->find($productId);

        if (!$product) {
            throw new \Exception('Produit non trouvé');
        }

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            // Vérifier si la quantité demandée est disponible
            $availableQuantity = $product->getQuantity();
            $quantity = min($quantity, $availableQuantity);

            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $quantity;
                $cart[$productId]['stock'] = $availableQuantity;
            }
        }

        $this->session->set('cart', $cart);

        return [
            'cart' => $cart,
            'total' => $this->calculateTotal(),
            'cartCount' => $this->getCartCount(),
            'itemTotal' => isset($cart[$productId]) ? $cart[$productId]['quantity'] * $cart[$productId]['price'] : 0
        ];
    }

    public function removeFromCart(int $productId): array
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->session->set('cart', $cart);
        }

        return [
            'cart' => $cart,
            'total' => $this->calculateTotal(),
            'cartCount' => $this->getCartCount()
        ];
    }

    public function clearCart(): void
    {
        $this->session->remove('cart');
    }

    public function getCartCount(): int
    {
        return array_sum(array_column($this->getCart(), 'quantity'));
    }

    public function calculateTotal(): float
    {
        $total = 0;
        foreach ($this->getCart() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function validateStock(): array
    {
        $cart = $this->getCart();
        $errors = [];
        $updatedCart = [];

        foreach ($cart as $productId => $item) {
            $product = $this->productsRepository->find($productId);
            if (!$product) {
                $errors[] = "Le produit {$item['name']} n'est plus disponible.";
                continue;
            }

            $availableQuantity = $product->getQuantity();
            if ($availableQuantity < $item['quantity']) {
                if ($availableQuantity > 0) {
                    $updatedCart[$productId] = [
                        'id' => $product->getId(),
                        'name' => $product->getTitle(),
                        'price' => $product->getPrice(),
                        'quantity' => $availableQuantity,
                        'stock' => $availableQuantity
                    ];
                    $errors[] = "La quantité du produit {$item['name']} a été ajustée à {$availableQuantity} (stock disponible).";
                } else {
                    $errors[] = "Le produit {$item['name']} n'est plus en stock.";
                }
            } else {
                $updatedCart[$productId] = $item;
            }
        }

        if (!empty($updatedCart)) {
            $this->session->set('cart', $updatedCart);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'cart' => $updatedCart
        ];
    }
} 