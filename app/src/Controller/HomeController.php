<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductsRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(SessionInterface $session, ProductsRepository $productsRepository): Response
    {
        $cart = $session->get('cart', []);
        $cartCount = array_sum(array_column($cart, 'quantity'));
        $latestProducts = $productsRepository->findBy([], ['id' => 'DESC'], 8);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'cartCount' => $cartCount,
            'products' => $latestProducts,
        ]);
    }
}