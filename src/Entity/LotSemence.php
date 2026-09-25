<?php

namespace App\Entity;

use App\Repository\LotSemenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LotSemenceRepository::class)]
#[ORM\Table(name: 'lot_semence')]
class LotSemence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'numero_lot', length: 80, unique: true)]
    #[Assert\NotBlank]
    private ?string $numeroLot = null;

    #[ORM\ManyToOne(targetEntity: Espece::class)]
    #[ORM\JoinColumn(name: 'id_espece', referencedColumnName: 'id', nullable: false)]
    private ?Espece $espece = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $provenance = null;

    #[ORM\Column(name: 'date_reception', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateReception = null;

    public function getId(): ?int { return $this->id; }

    public function getNumeroLot(): ?string { return $this->numeroLot; }
    public function setNumeroLot(string $v): static { $this->numeroLot = $v; return $this; }

    public function getEspece(): ?Espece { return $this->espece; }
    public function setEspece(?Espece $v): static { $this->espece = $v; return $this; }

    public function getProvenance(): ?string { return $this->provenance; }
    public function setProvenance(?string $v): static { $this->provenance = $v; return $this; }

    public function getDateReception(): ?\DateTimeImmutable { return $this->dateReception; }
    public function setDateReception(?\DateTimeImmutable $v): static { $this->dateReception = $v; return $this; }

    public function __toString(): string { return (string) $this->numeroLot; }
}
