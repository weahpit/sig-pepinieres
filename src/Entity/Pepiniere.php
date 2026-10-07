<?php

namespace App\Entity;

use App\Repository\PepiniereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PepiniereRepository::class)]
#[ORM\Table(name: 'pepiniere')]
class Pepiniere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'code_pepiniere', length: 50, unique: true)]
    #[Assert\NotBlank]
    private ?string $codePepiniere = null;

    #[ORM\Column(name: 'nom_pepiniere', length: 200)]
    #[Assert\NotBlank]
    private ?string $nomPepiniere = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $departement = null;

    #[ORM\Column(name: 'sous_prefecture', length: 120, nullable: true)]
    private ?string $sousPrefecture = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $village = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(name: 'superficie_m2', type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $superficieM2 = null;

    /**
     * Contour (parcelle) de la pépinière : liste ordonnée de points [longitude, latitude]
     * issue de la « Délimitation de la surface » saisie dans Kobo. null si non tracée.
     *
     * @var array<int, array{0: float, 1: float}>|null
     */
    #[ORM\Column(name: 'contour_geojson', type: 'json', nullable: true)]
    private ?array $contourGeojson = null;

    #[ORM\ManyToOne(targetEntity: Agent::class, inversedBy: 'pepinieres')]
    #[ORM\JoinColumn(name: 'id_responsable', referencedColumnName: 'id', nullable: true)]
    private ?Agent $responsable = null;

    #[ORM\Column(name: 'organisme_gestionnaire', length: 200, nullable: true)]
    private ?string $organismeGestionnaire = null;

    #[ORM\Column(name: 'date_creation', type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(name: 'capacite_production_an', nullable: true)]
    private ?int $capaciteProductionAn = null;

    #[ORM\Column(name: 'date_maj', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateMaj = null;

    /** @var Collection<int, Planche> */
    #[ORM\OneToMany(mappedBy: 'pepiniere', targetEntity: Planche::class, orphanRemoval: true)]
    private Collection $planches;

    /** @var Collection<int, Lot> */
    #[ORM\OneToMany(mappedBy: 'pepiniere', targetEntity: Lot::class, orphanRemoval: true)]
    private Collection $lots;

    /** @var Collection<int, Depense> */
    #[ORM\OneToMany(mappedBy: 'pepiniere', targetEntity: Depense::class, orphanRemoval: true)]
    private Collection $depenses;

    public function __construct()
    {
        $this->planches = new ArrayCollection();
        $this->lots = new ArrayCollection();
        $this->depenses = new ArrayCollection();
        $this->dateMaj = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getCodePepiniere(): ?string { return $this->codePepiniere; }
    public function setCodePepiniere(string $v): static { $this->codePepiniere = $v; return $this; }

    public function getNomPepiniere(): ?string { return $this->nomPepiniere; }
    public function setNomPepiniere(string $v): static { $this->nomPepiniere = $v; return $this; }

    public function getRegion(): ?string { return $this->region; }
    public function setRegion(?string $v): static { $this->region = $v; return $this; }

    public function getDepartement(): ?string { return $this->departement; }
    public function setDepartement(?string $v): static { $this->departement = $v; return $this; }

    public function getSousPrefecture(): ?string { return $this->sousPrefecture; }
    public function setSousPrefecture(?string $v): static { $this->sousPrefecture = $v; return $this; }

    public function getVillage(): ?string { return $this->village; }
    public function setVillage(?string $v): static { $this->village = $v; return $this; }

    public function getLatitude(): ?string { return $this->latitude; }
    public function setLatitude(?string $v): static { $this->latitude = $v; return $this; }

    public function getLongitude(): ?string { return $this->longitude; }
    public function setLongitude(?string $v): static { $this->longitude = $v; return $this; }

    public function getSuperficieM2(): ?string { return $this->superficieM2; }
    public function setSuperficieM2(?string $v): static { $this->superficieM2 = $v; return $this; }

    public function getContourGeojson(): ?array { return $this->contourGeojson; }
    public function setContourGeojson(?array $v): static { $this->contourGeojson = $v; return $this; }

    public function getResponsable(): ?Agent { return $this->responsable; }
    public function setResponsable(?Agent $v): static { $this->responsable = $v; return $this; }

    public function getOrganismeGestionnaire(): ?string { return $this->organismeGestionnaire; }
    public function setOrganismeGestionnaire(?string $v): static { $this->organismeGestionnaire = $v; return $this; }

    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
    public function setDateCreation(?\DateTimeImmutable $v): static { $this->dateCreation = $v; return $this; }

    public function getCapaciteProductionAn(): ?int { return $this->capaciteProductionAn; }
    public function setCapaciteProductionAn(?int $v): static { $this->capaciteProductionAn = $v; return $this; }

    public function getDateMaj(): ?\DateTimeImmutable { return $this->dateMaj; }
    public function setDateMaj(?\DateTimeImmutable $v): static { $this->dateMaj = $v; return $this; }

    /** @return Collection<int, Planche> */
    public function getPlanches(): Collection { return $this->planches; }

    /** @return Collection<int, Lot> */
    public function getLots(): Collection { return $this->lots; }

    /** @return Collection<int, Depense> */
    public function getDepenses(): Collection { return $this->depenses; }

    public function __toString(): string { return (string) $this->nomPepiniere; }
}
