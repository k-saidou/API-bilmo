<?php

namespace App\Controller;

use App\Entity\Produit;
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

    #[Route('/api/produits/{id}', name: 'detailProduit', methods: ['GET'])]
    public function getDetailProduit(Produit $produit, SerializerInterface $serializer): JsonResponse 
    {
        $jsonProduit = $serializer->serialize($produit, 'json');
        return new JsonResponse($jsonProduit, Response::HTTP_OK, [], true);
    }
}
