<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\Product\ProductFormType;
use App\Repository\ProductsRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product')]
class ProductController extends AbstractController
{
    public function __construct(
        private ProductService $productService,
        private ProductsRepository $productsRepository
    ) {}

    /**
     * Affiche tous les produits disponibles
     */
    #[Route('/', name: 'app_product_index')]
    public function index(): Response
    {
        // N'afficher que les produits disponibles
        $products = $this->productsRepository->findBy(['quantity' => 1]);

        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * Affiche les produits de l'utilisateur connecté
     */
    #[Route('/mes-articles', name: 'app_user_products')]
    #[IsGranted('ROLE_USER')]
    public function userProducts(): Response
    {
        $products = $this->productsRepository->findBy(
            ['users' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('product/user_products.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * Ajoute un nouveau produit
     */
    #[Route('/add', name: 'app_product_add')]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request): Response
    {
        $product = new Products();
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pour chaque média du produit, on gère l'upload ou l'URL
            foreach ($form->get('media') as $mediaForm) {
                $media = $mediaForm->getData();
                if (!$media) continue;
                $file = $mediaForm->get('file')->getData();
                if ($file) {
                    // Si un fichier est uploadé, on le déplace dans public/upload
                    $filename = uniqid().'.'.$file->guessExtension();
                    $file->move(
                        $this->getParameter('kernel.project_dir').'/public/upload',
                        $filename
                    );
                    // On stocke le nom du fichier dans filePath
                    $media->setFilePath($filename);
                } else {
                    // Sinon, on garde la valeur du champ texte (URL ou vide)
                    // Rien à faire, filePath est déjà rempli par le formulaire
                }
                // On lie le média au produit (sécurité)
                $media->setProducts($product);
            }
            $this->productService->createProduct($product, $this->getUser());
            $this->addFlash('success', 'Article ajouté avec succès !');
            return $this->redirectToRoute('app_user_products');
        }

        return $this->render('product/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Products $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * Modifie un produit existant
     */
    #[Route('/{id}/edit', name: 'app_product_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Products $product): Response
    {
        if (!$this->productService->canManageProduct($product, $this->getUser(), $this->isGranted('ROLE_ADMIN'))) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cet article.');
        }

        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pour chaque média du produit, on gère l'upload ou l'URL (comme dans add)
            foreach ($form->get('media') as $mediaForm) {
                $media = $mediaForm->getData();
                if (!$media) continue;
                $file = $mediaForm->get('file')->getData();
                if ($file) {
                    $filename = uniqid().'.'.$file->guessExtension();
                    $file->move(
                        $this->getParameter('kernel.project_dir').'/public/upload',
                        $filename
                    );
                    $media->setFilePath($filename);
                }
                $media->setProducts($product);
            }
            $this->productService->updateProduct($product);
            $this->addFlash('success', 'Article modifié avec succès !');
            return $this->redirectToRoute('app_user_products');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Supprime un produit
     */
    #[Route('/{id}/delete', name: 'app_product_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Products $product): Response
    {
        if (!$this->productService->canManageProduct($product, $this->getUser(), $this->isGranted('ROLE_ADMIN'))) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cet article.');
        }

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            if ($this->productService->deleteProduct($product)) {
                $this->addFlash('success', 'Article supprimé avec succès !');
            } else {
                $this->addFlash('error', 'Impossible de supprimer cet article car il est lié à une commande.');
            }
        }

        return $this->redirectToRoute('app_user_products');
    }

    /**
     * Active/Désactive un produit
     */
    #[Route('/{id}/toggle', name: 'app_product_toggle', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggle(Request $request, Products $product): Response
    {
        if (!$this->productService->canManageProduct($product, $this->getUser(), false)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cet article.');
        }

        $isVisible = $this->productService->toggleVisibility($product);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'isAvailable' => $isVisible
            ]);
        }

        return $this->redirectToRoute('app_user_products');
    }
} 