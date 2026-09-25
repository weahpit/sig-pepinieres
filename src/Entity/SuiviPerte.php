<?php

namespace App\Entity;

use App\Enum\CausePerte;
use App\Repository\SuiviPerteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SuiviPerteRepository::class)]
#[ORM\Table(name: 'suivi_perte')]
class SuiviPerte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Lot::class, inversedBy: 'pertes')]
    #[ORM\JoinColumn(name: 'id_lot', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Lot $lot = null;

    #[ORM\Column(name: 'date_perte', type: 'date_immutable')]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $datePerte = null;

    #[ORM\Column(type: 'string', length: 20, enumType: CausePerte::class)]
    #[Assert\NotNull]
    private ?CausePerte $cause = null;

    #[ORM\Column(name: 'nb_plants_perdus', nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $nbPlantsPerdus = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $pourcentage = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observations = null;

    public function getId(): ?int { return $this->id; }

    public function getLot(): ?Lot { return $this->lot; }
    public function setLot(?Lot $v): static { $this->lot = $v; return $this; }

    public function getDatePerte(): ?\DateTimeImmutable { return $this->datePerte; }
    public function setDatePerte(\DateTimeImmutable $v): static { $this->datePerte = $v; return $this; }

    public function getCause(): ?CausePerte { return $this->cause; }
    public function setCause(CausePerte $v): static { $this->cause = $v; return $this; }

    public function getNbPlantsPerdus(): ?int { return $this->nbPlantsPerdus; }
    public function setNbPlantsPerdus(?int $v): static { $this->nbPlantsPerdus = $v; return $this; }

    public function getPourcentage(): ?string { return $this->pourcentage; }
    public function setPourcentage(?string $v): static { $this->pourcentage = $v; return $this; }

    public function getObservations(): ?string { return $this->observations; }
    public function setObservations(?string $v): static { $this->observations = $v; return $this; }
}
