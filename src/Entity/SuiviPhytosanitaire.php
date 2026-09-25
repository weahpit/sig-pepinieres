<?php

namespace App\Entity;

use App\Repository\SuiviPhytosanitaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SuiviPhytosanitaireRepository::class)]
#[ORM\Table(name: 'suivi_phytosanitaire')]
class SuiviPhytosanitaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Lot::class, inversedBy: 'suivisPhyto')]
    #[ORM\JoinColumn(name: 'id_lot', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Lot $lot = null;

    #[ORM\Column(name: 'date_observation', type: 'date_immutable')]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $dateObservation = null;

    #[ORM\Column(name: 'maladie_ravageur', length: 200, nullable: true)]
    private ?string $maladieRavageur = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $symptomes = null;

    #[ORM\Column(name: 'traitement_applique', type: 'text', nullable: true)]
    private ?string $traitementApplique = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $produit = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $resultat = null;

    #[ORM\ManyToOne(targetEntity: Agent::class)]
    #[ORM\JoinColumn(name: 'id_agent', referencedColumnName: 'id', nullable: true)]
    private ?Agent $agent = null;

    public function getId(): ?int { return $this->id; }

    public function getLot(): ?Lot { return $this->lot; }
    public function setLot(?Lot $v): static { $this->lot = $v; return $this; }

    public function getDateObservation(): ?\DateTimeImmutable { return $this->dateObservation; }
    public function setDateObservation(\DateTimeImmutable $v): static { $this->dateObservation = $v; return $this; }

    public function getMaladieRavageur(): ?string { return $this->maladieRavageur; }
    public function setMaladieRavageur(?string $v): static { $this->maladieRavageur = $v; return $this; }

    public function getSymptomes(): ?string { return $this->symptomes; }
    public function setSymptomes(?string $v): static { $this->symptomes = $v; return $this; }

    public function getTraitementApplique(): ?string { return $this->traitementApplique; }
    public function setTraitementApplique(?string $v): static { $this->traitementApplique = $v; return $this; }

    public function getProduit(): ?string { return $this->produit; }
    public function setProduit(?string $v): static { $this->produit = $v; return $this; }

    public function getResultat(): ?string { return $this->resultat; }
    public function setResultat(?string $v): static { $this->resultat = $v; return $this; }

    public function getAgent(): ?Agent { return $this->agent; }
    public function setAgent(?Agent $v): static { $this->agent = $v; return $this; }
}
