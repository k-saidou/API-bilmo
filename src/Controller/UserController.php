<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'app_user', methods: ['GET'])]
    public function getProfil(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $profilList = $userRepository->findAll();
        $jsonProfilList = $serializer->serialize($profilList, 'json', ['groups' => 'getClient']);
        return new JsonResponse($jsonProfilList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getDetailProfil(User $user, SerializerInterface $serializer): JsonResponse 
    {
        $jsonProfilList = $serializer->serialize($user, 'json', ['groups' => 'getClient']);
        return new JsonResponse($jsonProfilList, Response::HTTP_OK, [], true);
    }
}
