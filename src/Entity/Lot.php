<?php

namespace App\Entity;

use App\Repository\LotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LotRepository::class)]
#[ORM\Table(name: 'lot')]
#[ORM\UniqueConstraint(name: 'uq_lot', columns: ['id_pepiniere', 'numero_lot'])]
#[ORM\HasLifecycleCallbacks]
class Lot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'numero_lot', length: 80)]
    #[Assert\NotBlank]
    private ?string $numeroLot = null;

    #[ORM\ManyToOne(targetEntity: Pepiniere::class, inversedBy: 'lots')]
    #[ORM\JoinColumn(name: 'id_pepiniere', referencedColumnName: 'id', nullable: false)]
    private ?Pepiniere $pepiniere = null;

    #[ORM\ManyToOne(targetEntity: Planche::class, inversedBy: 'lots')]
    #[ORM\JoinColumn(name: 'id_planche', referencedColumnName: 'id', nullable: true)]
    private ?Planche $planche = null;

    #[ORM\ManyToOne(targetEntity: Espece::class)]
    #[ORM\JoinColumn(name: 'id_espece', referencedColumnName: 'id', nullable: false)]
    private ?Espece $espece = null;

    #[ORM\ManyToOne(targetEntity: LotSemence::class)]
    #[ORM\JoinColumn(name: 'id_lot_semence', referencedColumnName: 'id', nullable: true)]
    private ?LotSemence $lotSemence = null;

    #[ORM\Column(name: 'date_semis', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateSemis = null;

    #[ORM\Column(name: 'nb_graines_semees', nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $nbGrainesSemees = null;

    #[ORM\Column(name: 'date_germination', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateGermination = null;

    #[ORM\Column(name: 'nb_plants_leves', nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $nbPlantsLeves = null;

    #[ORM\Column(name: 'taux_germination', type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $tauxGermination = null;

    #[ORM\Column(name: 'date_sortie', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateSortie = null;

    /** @var Collection<int, OperationCulturale> */
    #[ORM\OneToMany(mappedBy: 'lot', targetEntity: OperationCulturale::class, orphanRemoval: true)]
    private Collection $operations;

    /** @var Collection<int, SuiviCroissance> */
    #[ORM\OneToMany(mappedBy: 'lot', targetEntity: SuiviCroissance::class, orphanRemoval: true)]
    private Collection $suivisCroissance;

    /** @var Collection<int, SuiviPhytosanitaire> */
    #[ORM\OneToMany(mappedBy: 'lot', targetEntity: SuiviPhytosanitaire::class, orphanRemoval: true)]
    private Collection $suivisPhyto;

    /** @var Collection<int, SuiviPerte> */
    #[ORM\OneToMany(mappedBy: 'lot', targetEntity: SuiviPerte::class, orphanRemoval: true)]
    private Collection $pertes;

    public function __construct()
    {
        $this->operations = new ArrayCollection();
        $this->suivisCroissance = new ArrayCollection();
        $this->suivisPhyto = new ArrayCollection();
        $this->pertes = new ArrayCollection();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function computeTauxGermination(): void
    {
        if ($this->nbGrainesSemees && $this->nbGrainesSemees > 0 && $this->nbPlantsLeves !== null) {
            // Le taux peut dépasser 100 % si les données de terrain sont
            // incohérentes (plus de plants vivants que de plants mis en
            // production). On borne à 999.99 pour rester dans les limites de
            // la colonne decimal(5,2) et éviter une erreur SQL « out of range ».
            $taux = min($this->nbPlantsLeves * 100 / $this->nbGrainesSemees, 999.99);
            $this->tauxGermination = number_format(
                $taux,
                2,
                '.',
                ''
            );
        } else {
            $this->tauxGermination = null;
        }
    }

    public function getId(): ?int { return $this->id; }

    public function getNumeroLot(): ?string { return $this->numeroLot; }
    public function setNumeroLot(string $v): static { $this->numeroLot = $v; return $this; }

    public function getPepiniere(): ?Pepiniere { return $this->pepiniere; }
    public function setPepiniere(?Pepiniere $v): static { $this->pepiniere = $v; return $this; }

    public function getPlanche(): ?Planche { return $this->planche; }
    public function setPlanche(?Planche $v): static { $this->planche = $v; return $this; }

    public function getEspece(): ?Espece { return $this->espece; }
    public function setEspece(?Espece $v): static { $this->espece = $v; return $this; }

    public function getLotSemence(): ?LotSemence { return $this->lotSemence; }
    public function setLotSemence(?LotSemence $v): static { $this->lotSemence = $v; return $this; }

    public function getDateSemis(): ?\DateTimeImmutable { return $this->dateSemis; }
    public function setDateSemis(?\DateTimeImmutable $v): static { $this->dateSemis = $v; return $this; }

    public function getNbGrainesSemees(): ?int { return $this->nbGrainesSemees; }
    public function setNbGrainesSemees(?int $v): static { $this->nbGrainesSemees = $v; return $this; }

    public function getDateGermination(): ?\DateTimeImmutable { return $this->dateGermination; }
    public function setDateGermination(?\DateTimeImmutable $v): static { $this->dateGermination = $v; return $this; }

    public function getNbPlantsLeves(): ?int { return $this->nbPlantsLeves; }
    public function setNbPlantsLeves(?int $v): static { $this->nbPlantsLeves = $v; return $this; }

    public function getTauxGermination(): ?string { return $this->tauxGermination; }

    public function getDateSortie(): ?\DateTimeImmutable { return $this->dateSortie; }
    public function setDateSortie(?\DateTimeImmutable $v): static { $this->dateSortie = $v; return $this; }

    /** @return Collection<int, OperationCulturale> */
    public function getOperations(): Collection { return $this->operations; }

    /** @return Collection<int, SuiviCroissance> */
    public function getSuivisCroissance(): Collection { return $this->suivisCroissance; }

    /** @return Collection<int, SuiviPhytosanitaire> */
    public function getSuivisPhyto(): Collection { return $this->suivisPhyto; }

    /** @return Collection<int, SuiviPerte> */
    public function getPertes(): Collection { return $this->pertes; }

    public function __toString(): string { return (string) $this->numeroLot; }
}
