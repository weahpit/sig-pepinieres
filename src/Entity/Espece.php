<?php

namespace App\Entity;

use App\Repository\EspeceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EspeceRepository::class)]
#[ORM\Table(name: 'espece')]
#[ORM\UniqueConstraint(name: 'uq_espece_nom', columns: ['nom_commun', 'nom_scientifique'])]
class Espece
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'nom_commun', length: 150)]
    #[Assert\NotBlank]
    private ?string $nomCommun = null;

    #[ORM\Column(name: 'nom_scientifique', length: 200, nullable: true)]
    private ?string $nomScientifique = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $famille = null;

    public function getId(): ?int { return $this->id; }

    public function getNomCommun(): ?string { return $this->nomCommun; }
    public function setNomCommun(string $v): static { $this->nomCommun = $v; return $this; }

    public function getNomScientifique(): ?string { return $this->nomScientifique; }
    public function setNomScientifique(?string $v): static { $this->nomScientifique = $v; return $this; }

    public function getFamille(): ?string { return $this->famille; }
    public function setFamille(?string $v): static { $this->famille = $v; return $this; }

    public function __toString(): string { return (string) $this->nomCommun; }
}
