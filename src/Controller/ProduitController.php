<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProduitController extends AbstractController
{
    #[Route('/api/produits', name: 'app_produit', methods: ['GET'])]
    public function getProduit(ProduitRepository $produitRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $idCache = "getAllProduit-" . $page . "-" . $limit;

        $jsonProduitList = $cache->get($idCache, function (ItemInterface $item) use ($produitRepository, $page, $limit, $serializer) {
            $item->tag("produitCache");
            $produitList = $produitRepository->findAllPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(['getProduit']);
            return $serializer->serialize($produitList, 'json', $context);
        });

        return new JsonResponse($jsonProduitList, Response::HTTP_OK, [], true);

    }

    #[Route('/api/produits/{id}', name: 'detailProduit', methods: ['GET'])]
    public function getDetailProduit(Produit $produit, SerializerInterface $serializer): JsonResponse 
    {
        $context = SerializationContext::create()->setGroups(['getProduit']);
        $jsonProduit = $serializer->serialize($produit, 'json', $context);
        return new JsonResponse($jsonProduit, Response::HTTP_OK, [], true);
    }
}
