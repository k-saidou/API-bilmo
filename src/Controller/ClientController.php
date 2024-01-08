<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'app_client', methods: ['GET'])]
    public function getClient(ClientRepository $clientRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $idCache = "getAllClient-" . $page . "-" . $limit;

        $jsonClientList = $cache->get($idCache, function (ItemInterface $item) use ($clientRepository, $page, $limit, $serializer) {
            $item->tag("clientCache");
            $clientList = $clientRepository->findAllPagination($page, $limit);  
            $context = SerializationContext::create()->setGroups(['getUser']);

            return $serializer->serialize($clientList, 'json', $context);
        });

        return new JsonResponse($jsonClientList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{id}', name: 'detailClient', methods: ['GET'])]
    public function getDetailClient(Client $client, SerializerInterface $serializer): JsonResponse 
    {
        $context = SerializationContext::create()->setGroups(['getUser']);
        $jsonClient = $serializer->serialize($client, 'json', $context);
        
        return new JsonResponse($jsonClient, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{id}', name: 'deleteClient', methods: ['DELETE'])]
    public function deleteClient(Client $client, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse 
    {
        $cachePool->invalidateTags(["ClientCache"]);
        $em->remove($client);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/clients', name:"createClient", methods: ['POST'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour créer un livre')]
    public function createClient(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, UserRepository $userRepository, ValidatorInterface $validator): JsonResponse 
    {

        $client = $serializer->deserialize($request->getContent(), Client::class, 'json');
        // Récupération de l'ensemble des données envoyées sous forme de tableau

        // On vérifie les erreurs
        $errors = $validator->validate($client);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($client);
        $em->flush();

        $content = $request->toArray();
        // Récupération de l'userClient. S'il n'est pas défini, alors on met -1 par défaut.
        $userClient = $content['userClient'] ?? -1;
        // On cherche l'user qui correspond et on l'assigne au client.
        // Si "find" ne trouve pas l'auteur, alors null sera retourné.
        $client->setUserClient($userRepository->find($userClient));

        $context = SerializationContext::create()->setGroups(['getUser']);
        $jsonClient = $serializer->serialize($client, 'json', $context);
        $location = $urlGenerator->generate('detailClient', ['id' => $client->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonClient, Response::HTTP_CREATED, ["Location" => $location], true);
   }
}
