<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;


#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUser", "getClient"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(["getUser", "getClient"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(["getUser", "getClient"])]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(["getUser", "getClient"])]
    private ?string $email;

    // #[ORM\ManyToOne(inversedBy: 'clients')]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'users', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getClient"])]
    #[MaxDepth(1)] // Limite la profondeur de sérialisation
    private ?User $userClient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserClient(): ?User
    {
        return $this->userClient;
    }

    public function setUserClient(?User $userClient): static
    {
        $this->userClient = $userClient;

        return $this;
    }
}
