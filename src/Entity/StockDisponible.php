<?php

namespace App\Entity;

use App\Repository\StockDisponibleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockDisponibleRepository::class)]
#[ORM\Table(name: 'stock_disponible')]
class StockDisponible
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Lot::class)]
    #[ORM\JoinColumn(name: 'id_lot', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Lot $lot = null;

    #[ORM\ManyToOne(targetEntity: Espece::class)]
    #[ORM\JoinColumn(name: 'id_espece', referencedColumnName: 'id', nullable: false)]
    private ?Espece $espece = null;

    #[ORM\Column(name: 'plants_disponibles')]
    #[Assert\PositiveOrZero]
    private int $plantsDisponibles = 0;

    #[ORM\Column(name: 'plants_reserves')]
    #[Assert\PositiveOrZero]
    private int $plantsReserves = 0;

    #[ORM\Column(name: 'plants_distribues')]
    #[Assert\PositiveOrZero]
    private int $plantsDistribues = 0;

    #[ORM\Column(name: 'date_maj', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateMaj = null;

    public function __construct()
    {
        $this->dateMaj = new \DateTimeImmutable();
    }

    public function getSolde(): int
    {
        return $this->plantsDisponibles - $this->plantsReserves - $this->plantsDistribues;
    }

    public function getId(): ?int { return $this->id; }

    public function getLot(): ?Lot { return $this->lot; }
    public function setLot(?Lot $v): static { $this->lot = $v; return $this; }

    public function getEspece(): ?Espece { return $this->espece; }
    public function setEspece(?Espece $v): static { $this->espece = $v; return $this; }

    public function getPlantsDisponibles(): int { return $this->plantsDisponibles; }
    public function setPlantsDisponibles(int $v): static { $this->plantsDisponibles = $v; return $this; }

    public function getPlantsReserves(): int { return $this->plantsReserves; }
    public function setPlantsReserves(int $v): static { $this->plantsReserves = $v; return $this; }

    public function getPlantsDistribues(): int { return $this->plantsDistribues; }
    public function setPlantsDistribues(int $v): static { $this->plantsDistribues = $v; return $this; }

    public function getDateMaj(): ?\DateTimeImmutable { return $this->dateMaj; }
    public function setDateMaj(?\DateTimeImmutable $v): static { $this->dateMaj = $v; return $this; }
}
