<?php

namespace App\Entity;

use App\Repository\OperationCulturaleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OperationCulturaleRepository::class)]
#[ORM\Table(name: 'operation_culturale')]
class OperationCulturale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Lot::class, inversedBy: 'operations')]
    #[ORM\JoinColumn(name: 'id_lot', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Lot $lot = null;

    #[ORM\Column(name: 'date_operation', type: 'date_immutable')]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $dateOperation = null;

    #[ORM\Column]
    private bool $arrosage = false;

    #[ORM\Column]
    private bool $desherbage = false;

    #[ORM\Column]
    private bool $fertilisation = false;

    #[ORM\Column(name: 'traitement_phyto')]
    private bool $traitementPhyto = false;

    #[ORM\Column]
    private bool $repiquage = false;

    #[ORM\Column]
    private bool $ombrage = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observations = null;

    #[ORM\ManyToOne(targetEntity: Agent::class)]
    #[ORM\JoinColumn(name: 'id_agent', referencedColumnName: 'id', nullable: true)]
    private ?Agent $agent = null;

    public function getId(): ?int { return $this->id; }

    public function getLot(): ?Lot { return $this->lot; }
    public function setLot(?Lot $v): static { $this->lot = $v; return $this; }

    public function getDateOperation(): ?\DateTimeImmutable { return $this->dateOperation; }
    public function setDateOperation(\DateTimeImmutable $v): static { $this->dateOperation = $v; return $this; }

    public function isArrosage(): bool { return $this->arrosage; }
    public function setArrosage(bool $v): static { $this->arrosage = $v; return $this; }

    public function isDesherbage(): bool { return $this->desherbage; }
    public function setDesherbage(bool $v): static { $this->desherbage = $v; return $this; }

    public function isFertilisation(): bool { return $this->fertilisation; }
    public function setFertilisation(bool $v): static { $this->fertilisation = $v; return $this; }

    public function isTraitementPhyto(): bool { return $this->traitementPhyto; }
    public function setTraitementPhyto(bool $v): static { $this->traitementPhyto = $v; return $this; }

    public function isRepiquage(): bool { return $this->repiquage; }
    public function setRepiquage(bool $v): static { $this->repiquage = $v; return $this; }

    public function isOmbrage(): bool { return $this->ombrage; }
    public function setOmbrage(bool $v): static { $this->ombrage = $v; return $this; }

    public function getObservations(): ?string { return $this->observations; }
    public function setObservations(?string $v): static { $this->observations = $v; return $this; }

    public function getAgent(): ?Agent { return $this->agent; }
    public function setAgent(?Agent $v): static { $this->agent = $v; return $this; }
}
