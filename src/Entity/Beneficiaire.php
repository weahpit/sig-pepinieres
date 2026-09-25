<?php

namespace App\Entity;

use App\Repository\BeneficiaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BeneficiaireRepository::class)]
#[ORM\Table(name: 'beneficiaire')]
class Beneficiaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    private ?string $nom = null;

    #[ORM\Column(name: 'type_beneficiaire', length: 80, nullable: true)]
    private ?string $typeBeneficiaire = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $localite = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $contact = null;

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $v): static { $this->nom = $v; return $this; }

    public function getTypeBeneficiaire(): ?string { return $this->typeBeneficiaire; }
    public function setTypeBeneficiaire(?string $v): static { $this->typeBeneficiaire = $v; return $this; }

    public function getLocalite(): ?string { return $this->localite; }
    public function setLocalite(?string $v): static { $this->localite = $v; return $this; }

    public function getContact(): ?string { return $this->contact; }
    public function setContact(?string $v): static { $this->contact = $v; return $this; }

    public function __toString(): string { return (string) $this->nom; }
}
