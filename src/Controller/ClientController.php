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
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class ClientController extends AbstractController
{


    private $jwtManager;
    private $tokenStorageInterface;


    public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
    }

    #[Route('/api/clients', name: 'app_client', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour voir les clients')]
    public function getClient(ClientRepository $clientRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse("User not authenticated", JsonResponse::HTTP_UNAUTHORIZED);
        }

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $idCache = "getAllClient-" . $page . "-" . $limit;

        $jsonClientList = $cache->get($idCache, function (ItemInterface $item) use ($clientRepository, $user, $page, $limit, $serializer) {
            $item->tag("clientCache");
            $clientList = $clientRepository->findAllPagination($page, $limit);

            $clientList = array_filter($clientList, function ($client) use ($user) {
                return $client->getUserClient() === $user;
            });

            $context = SerializationContext::create()->setGroups(['getUser']);

            return $serializer->serialize($clientList, 'json', $context);
        });

        return new JsonResponse($jsonClientList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{id}', name: 'detailClient', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour voir les details')]
    public function getDetailClient(Client $client, SerializerInterface $serializer): JsonResponse 
    {
        $context = SerializationContext::create()->setGroups(['getUser']);
        $jsonClient = $serializer->serialize($client, 'json', $context);
        
        return new JsonResponse($jsonClient, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{id}', name: 'deleteClient', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour supprimer un client')]
    public function deleteClient(Client $client, SerializerInterface $serializer, UserRepository $userRepository, EntityManagerInterface $em, TagAwareCacheInterface $cachePool, ValidatorInterface $validator): JsonResponse 
    {
        $token = $this->tokenStorageInterface->getToken();
        if (!$token) {
            return new JsonResponse("Invalid token", JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $decodedJwtToken = $this->jwtManager->decode($token);
        $username = $decodedJwtToken["username"];
        
        $user = $userRepository->findOneByEmail($username);
        if (!$user) {
            return new JsonResponse("User not found", JsonResponse::HTTP_NOT_FOUND);
        }
        
        if ($client->getUserClient() !== $user) {
            return new JsonResponse("Unauthorized", JsonResponse::HTTP_UNAUTHORIZED);
        }

        $errors = $validator->validate($client);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $cachePool->invalidateTags(["ClientCache"]);
        
        $em->remove($client);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/clients', name:"createClient", methods: ['POST'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour créer un client')]
    public function createClient(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, UserRepository $userRepository, ValidatorInterface $validator): JsonResponse 
    {

        $client = $serializer->deserialize($request->getContent(), Client::class, 'json');

        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $client->setUserClient($userRepository->findOneByEmail($decodedJwtToken["username"]));

        $errors = $validator->validate($client);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($client);
        $em->flush();

        $context = SerializationContext::create()->setGroups(['getUser']);
        $jsonClient = $serializer->serialize($client, 'json', $context);
        $location = $urlGenerator->generate('detailClient', ['id' => $client->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonClient, Response::HTTP_CREATED, ["Location" => $location], true);
   }
}
