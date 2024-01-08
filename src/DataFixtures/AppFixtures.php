<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Produit;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;


class AppFixtures extends Fixture
{
    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Création des users.
        $listUser = [];
    
        for ($i = 0; $i < 3; $i++) {
            $user = new User();
            $user->setNom($faker->firstName);
            $user->setPrenom($faker->lastName);
            $user->setEmail($faker->email);
            $user->setRaisonSocial($faker->company);
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
            $manager->persist($user);
    
            // On sauvegarde les users créés dans un tableau.
            $listUser[] = $user;
        }

        // Création des clients.
        for ($i = 0; $i < 45; $i++) {
            $client = new Client();
            $client->setNom($faker->firstName);
            $client->setPrenom($faker->lastName);
            $client->setEmail($faker->email);
            $client->setUserClient($listUser[array_rand($listUser)]);
            $manager->persist($client);
        }

        // Création des produits.
        for ($i = 0; $i < 30; $i++) {
            $produit = new Produit();
            $produit->setNom($faker->words(1, true));
            $produit->setDetail($faker->words(20, true));
            $produit->setPrix($faker->randomNumber(3, true));
            $manager->persist($produit);
        }

        $manager->flush();
    }
}
