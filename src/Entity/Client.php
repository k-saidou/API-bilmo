<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "detailClient",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getUser")
 * )
 *
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "deleteClient",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getUser", excludeIf = "expr(not is_granted('ROLE_USER'))"),
 * )
 *
 */
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
    #[Assert\NotBlank(message: "Le nom du client est obligatoire")]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(["getUser", "getClient"])]
    #[Assert\NotBlank(message: "Le prenom du client est obligatoire")]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(["getUser", "getClient"])]
    #[Assert\NotBlank(message: "L'email du client est obligatoire")]
    private ?string $email;

    // #[ORM\ManyToOne(inversedBy: 'clients')]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'users', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getClient"])]
    #[MaxDepth(1)] // Limite la profondeur de sérialisation
    #[Assert\NotBlank(message: "L'id de l'user est manquant")]

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
