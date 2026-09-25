<?php

namespace App\Entity;

use App\Repository\PlancheRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlancheRepository::class)]
#[ORM\Table(name: 'planche')]
#[ORM\UniqueConstraint(name: 'uq_planche', columns: ['id_pepiniere', 'numero_planche'])]
class Planche
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Pepiniere::class, inversedBy: 'planches')]
    #[ORM\JoinColumn(name: 'id_pepiniere', referencedColumnName: 'id', nullable: false)]
    private ?Pepiniere $pepiniere = null;

    #[ORM\Column(name: 'numero_planche', length: 50)]
    #[Assert\NotBlank]
    private ?string $numeroPlanche = null;

    #[ORM\Column(name: 'code_qr', length: 120, nullable: true)]
    private ?string $codeQr = null;

    /** @var Collection<int, Lot> */
    #[ORM\OneToMany(mappedBy: 'planche', targetEntity: Lot::class)]
    private Collection $lots;

    public function __construct()
    {
        $this->lots = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getPepiniere(): ?Pepiniere { return $this->pepiniere; }
    public function setPepiniere(?Pepiniere $v): static { $this->pepiniere = $v; return $this; }

    public function getNumeroPlanche(): ?string { return $this->numeroPlanche; }
    public function setNumeroPlanche(string $v): static { $this->numeroPlanche = $v; return $this; }

    public function getCodeQr(): ?string { return $this->codeQr; }
    public function setCodeQr(?string $v): static { $this->codeQr = $v; return $this; }

    /** @return Collection<int, Lot> */
    public function getLots(): Collection { return $this->lots; }

    public function __toString(): string { return (string) $this->numeroPlanche; }
}
