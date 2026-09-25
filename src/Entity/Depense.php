<?php

namespace App\Entity;

use App\Repository\DepenseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DepenseRepository::class)]
#[ORM\Table(name: 'depense')]
class Depense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Pepiniere::class, inversedBy: 'depenses')]
    #[ORM\JoinColumn(name: 'id_pepiniere', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Pepiniere $pepiniere = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    private ?string $categorie = null;

    #[ORM\Column(type: 'decimal', precision: 14, scale: 2)]
    #[Assert\PositiveOrZero]
    private ?string $montant = null;

    #[ORM\Column(name: 'date_depense', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateDepense = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observations = null;

    public function getId(): ?int { return $this->id; }

    public function getPepiniere(): ?Pepiniere { return $this->pepiniere; }
    public function setPepiniere(?Pepiniere $v): static { $this->pepiniere = $v; return $this; }

    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(string $v): static { $this->categorie = $v; return $this; }

    public function getMontant(): ?string { return $this->montant; }
    public function setMontant(string $v): static { $this->montant = $v; return $this; }

    public function getDateDepense(): ?\DateTimeImmutable { return $this->dateDepense; }
    public function setDateDepense(?\DateTimeImmutable $v): static { $this->dateDepense = $v; return $this; }

    public function getObservations(): ?string { return $this->observations; }
    public function setObservations(?string $v): static { $this->observations = $v; return $this; }
}
