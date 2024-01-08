<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProduitController extends AbstractController
{
    #[Route('/api/produits', name: 'app_produit', methods: ['GET'])]
    public function getProduit(ProduitRepository $produitRepository, SerializerInterface $serializer): JsonResponse
    {
        $produitList = $produitRepository->findAll();
        $jsonProduitList = $serializer->serialize($produitList, 'json');
        return new JsonResponse($jsonProduitList, Response::HTTP_OK, [], true);
    }
}
