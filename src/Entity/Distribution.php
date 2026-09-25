<?php

namespace App\Entity;

use App\Repository\DistributionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DistributionRepository::class)]
#[ORM\Table(name: 'distribution')]
class Distribution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Lot::class)]
    #[ORM\JoinColumn(name: 'id_lot', referencedColumnName: 'id', nullable: true)]
    private ?Lot $lot = null;

    #[ORM\ManyToOne(targetEntity: Espece::class)]
    #[ORM\JoinColumn(name: 'id_espece', referencedColumnName: 'id', nullable: false)]
    private ?Espece $espece = null;

    #[ORM\ManyToOne(targetEntity: Beneficiaire::class)]
    #[ORM\JoinColumn(name: 'id_beneficiaire', referencedColumnName: 'id', nullable: false)]
    private ?Beneficiaire $beneficiaire = null;

    #[ORM\Column(name: 'date_distribution', type: 'date_immutable')]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $dateDistribution = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $localite = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?int $quantite = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $projet = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $signature = null;

    #[ORM\ManyToOne(targetEntity: Agent::class)]
    #[ORM\JoinColumn(name: 'id_agent', referencedColumnName: 'id', nullable: true)]
    private ?Agent $agent = null;

    public function getId(): ?int { return $this->id; }

    public function getLot(): ?Lot { return $this->lot; }
    public function setLot(?Lot $v): static { $this->lot = $v; return $this; }

    public function getEspece(): ?Espece { return $this->espece; }
    public function setEspece(?Espece $v): static { $this->espece = $v; return $this; }

    public function getBeneficiaire(): ?Beneficiaire { return $this->beneficiaire; }
    public function setBeneficiaire(?Beneficiaire $v): static { $this->beneficiaire = $v; return $this; }

    public function getDateDistribution(): ?\DateTimeImmutable { return $this->dateDistribution; }
    public function setDateDistribution(\DateTimeImmutable $v): static { $this->dateDistribution = $v; return $this; }

    public function getLocalite(): ?string { return $this->localite; }
    public function setLocalite(?string $v): static { $this->localite = $v; return $this; }

    public function getQuantite(): ?int { return $this->quantite; }
    public function setQuantite(int $v): static { $this->quantite = $v; return $this; }

    public function getProjet(): ?string { return $this->projet; }
    public function setProjet(?string $v): static { $this->projet = $v; return $this; }

    public function getSignature(): ?string { return $this->signature; }
    public function setSignature(?string $v): static { $this->signature = $v; return $this; }

    public function getAgent(): ?Agent { return $this->agent; }
    public function setAgent(?Agent $v): static { $this->agent = $v; return $this; }
}
