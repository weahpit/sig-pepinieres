<?php

namespace App\Entity;

use App\Repository\PhotoGeoreferenceeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PhotoGeoreferenceeRepository::class)]
#[ORM\Table(name: 'photo_georeferencee')]
class PhotoGeoreferencee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Planche::class)]
    #[ORM\JoinColumn(name: 'id_planche', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Planche $planche = null;

    #[ORM\ManyToOne(targetEntity: Lot::class)]
    #[ORM\JoinColumn(name: 'id_lot', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Lot $lot = null;

    #[ORM\ManyToOne(targetEntity: Pepiniere::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(name: 'id_pepiniere', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Pepiniere $pepiniere = null;

    #[ORM\Column(name: 'legende', length: 255, nullable: true)]
    private ?string $legende = null;

    #[ORM\Column(name: 'url_photo', type: 'text')]
    #[Assert\NotBlank]
    private ?string $urlPhoto = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(name: 'date_prise', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $datePrise = null;

    #[ORM\ManyToOne(targetEntity: Agent::class)]
    #[ORM\JoinColumn(name: 'id_agent', referencedColumnName: 'id', nullable: true)]
    private ?Agent $agent = null;

    public function __construct()
    {
        $this->datePrise = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getPlanche(): ?Planche { return $this->planche; }
    public function setPlanche(?Planche $v): static { $this->planche = $v; return $this; }

    public function getLot(): ?Lot { return $this->lot; }
    public function setLot(?Lot $v): static { $this->lot = $v; return $this; }

    public function getPepiniere(): ?Pepiniere { return $this->pepiniere; }
    public function setPepiniere(?Pepiniere $v): static { $this->pepiniere = $v; return $this; }

    public function getLegende(): ?string { return $this->legende; }
    public function setLegende(?string $v): static { $this->legende = $v; return $this; }

    public function getUrlPhoto(): ?string { return $this->urlPhoto; }
    public function setUrlPhoto(string $v): static { $this->urlPhoto = $v; return $this; }

    public function getLatitude(): ?string { return $this->latitude; }
    public function setLatitude(?string $v): static { $this->latitude = $v; return $this; }

    public function getLongitude(): ?string { return $this->longitude; }
    public function setLongitude(?string $v): static { $this->longitude = $v; return $this; }

    public function getDatePrise(): ?\DateTimeImmutable { return $this->datePrise; }
    public function setDatePrise(?\DateTimeImmutable $v): static { $this->datePrise = $v; return $this; }

    public function getAgent(): ?Agent { return $this->agent; }
    public function setAgent(?Agent $v): static { $this->agent = $v; return $this; }
}
