<?php

namespace App\Entity;

use App\Enum\EtatSanitaire;
use App\Repository\SuiviCroissanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SuiviCroissanceRepository::class)]
#[ORM\Table(name: 'suivi_croissance')]
class SuiviCroissance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Lot::class, inversedBy: 'suivisCroissance')]
    #[ORM\JoinColumn(name: 'id_lot', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Lot $lot = null;

    #[ORM\Column(name: 'date_mesure', type: 'date_immutable')]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $dateMesure = null;

    #[ORM\Column(name: 'hauteur_moy_cm', type: 'decimal', precision: 6, scale: 2, nullable: true)]
    private ?string $hauteurMoyCm = null;

    #[ORM\Column(name: 'diametre_collet_mm', type: 'decimal', precision: 6, scale: 2, nullable: true)]
    private ?string $diametreColletMm = null;

    #[ORM\Column(name: 'nb_feuilles', nullable: true)]
    private ?int $nbFeuilles = null;

    #[ORM\Column(name: 'etat_sanitaire', type: 'string', length: 20, enumType: EtatSanitaire::class, nullable: true)]
    private ?EtatSanitaire $etatSanitaire = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $observations = null;

    #[ORM\ManyToOne(targetEntity: Agent::class)]
    #[ORM\JoinColumn(name: 'id_agent', referencedColumnName: 'id', nullable: true)]
    private ?Agent $agent = null;

    public function getId(): ?int { return $this->id; }

    public function getLot(): ?Lot { return $this->lot; }
    public function setLot(?Lot $v): static { $this->lot = $v; return $this; }

    public function getDateMesure(): ?\DateTimeImmutable { return $this->dateMesure; }
    public function setDateMesure(\DateTimeImmutable $v): static { $this->dateMesure = $v; return $this; }

    public function getHauteurMoyCm(): ?string { return $this->hauteurMoyCm; }
    public function setHauteurMoyCm(?string $v): static { $this->hauteurMoyCm = $v; return $this; }

    public function getDiametreColletMm(): ?string { return $this->diametreColletMm; }
    public function setDiametreColletMm(?string $v): static { $this->diametreColletMm = $v; return $this; }

    public function getNbFeuilles(): ?int { return $this->nbFeuilles; }
    public function setNbFeuilles(?int $v): static { $this->nbFeuilles = $v; return $this; }

    public function getEtatSanitaire(): ?EtatSanitaire { return $this->etatSanitaire; }
    public function setEtatSanitaire(?EtatSanitaire $v): static { $this->etatSanitaire = $v; return $this; }

    public function getObservations(): ?string { return $this->observations; }
    public function setObservations(?string $v): static { $this->observations = $v; return $this; }

    public function getAgent(): ?Agent { return $this->agent; }
    public function setAgent(?Agent $v): static { $this->agent = $v; return $this; }
}
