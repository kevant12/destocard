<?php

namespace App\Service;

use App\Entity\Products;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function createProduct(Products $product, Users $user): void
    {
        $product->setUsers($user);
        $product->setCreatedAt(new \DateTimeImmutable());
        
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    public function updateProduct(Products $product): void
    {
        $this->entityManager->flush();
    }

    public function deleteProduct(Products $product): bool
    {
        if ($product->getOrdersProducts()) {
            return false;
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();
        return true;
    }

    public function toggleVisibility(Products $product): bool
    {
        $product->setQuantity($product->getQuantity() > 0 ? 0 : 1);
        $this->entityManager->flush();
        
        return $product->getQuantity() > 0;
    }

    public function canManageProduct(Products $product, Users $user, bool $isAdmin = false): bool
    {
        return $product->getUsers() === $user || $isAdmin;
    }
} 