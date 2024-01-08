<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'app_client', methods: ['GET'])]
    public function getClient(ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        $clientList = $clientRepository->findAll();
        $jsonClientList = $serializer->serialize($clientList, 'json', ['groups' => 'getUser']);

        return new JsonResponse($jsonClientList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{id}', name: 'detailClient', methods: ['GET'])]
    public function getDetailClient(Client $client, SerializerInterface $serializer): JsonResponse 
    {
        $jsonClient = $serializer->serialize($client, 'json', ['groups' => 'getUser']);
        
        return new JsonResponse($jsonClient, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{id}', name: 'deleteClient', methods: ['DELETE'])]
    public function deleteClient(Client $client, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($client);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/clients', name:"createClient", methods: ['POST'])]
    public function createClient(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, UserRepository $userRepository): JsonResponse 
    {

        $client = $serializer->deserialize($request->getContent(), Client::class, 'json');
        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();
        // Récupération de l'userClient. S'il n'est pas défini, alors on met -1 par défaut.
        $userClient = $content['userClient'] ?? -1;
        // On cherche l'user qui correspond et on l'assigne au client.
        // Si "find" ne trouve pas l'auteur, alors null sera retourné.
        $client->setUserClient($userRepository->find($userClient));

        $em->persist($client);
        $em->flush();

        $jsonClient = $serializer->serialize($client, 'json', ['groups' => 'getClient']);
        $location = $urlGenerator->generate('detailClient', ['id' => $client->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonClient, Response::HTTP_CREATED, ["Location" => $location], true);
   }
}
