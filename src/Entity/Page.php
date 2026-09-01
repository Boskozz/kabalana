<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PageRepository;

#[ORM\Entity(PageRepository::class)]
#[ORM\Table(name: 'pages')]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 190, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 100)]
    private ?string $section = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $subSection = null;  // 👈 Nouvelle propriété

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?int $parentId = null;

    #[ORM\Column]
    private ?int $position = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'page', targetEntity: Bloc::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $blocs;

    public function __construct()
    {
        $this->blocs = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->isActive = true;
    }

    public function __toString(): string
    {
        return $this->title ?? 'Page sans titre';
    }

    // Getters et setters
    public function getId(): ?int { return $this->id; }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getSection(): ?string { return $this->section; }
    public function setSection(string $section): self { $this->section = $section; return $this; }

    public function getSubSection(): ?string { return $this->subSection; }
    public function setSubSection(?string $subSection): self { $this->subSection = $subSection; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): self { $this->type = $type; return $this; }

    public function getParentId(): ?int { return $this->parentId; }
    public function setParentId(?int $parentId): self { $this->parentId = $parentId; return $this; }

    public function getPosition(): ?int { return $this->position; }
    public function setPosition(int $position): self { $this->position = $position; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): self { $this->isActive = $isActive; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    public function getBlocs(): Collection { return $this->blocs; }
    public function addBloc(Bloc $bloc): self { if (!$this->blocs->contains($bloc)) { $this->blocs->add($bloc); $bloc->setPage($this); } return $this; }
    public function removeBloc(Bloc $bloc): self { if ($this->blocs->removeElement($bloc)) { if ($bloc->getPage() === $this) { $bloc->setPage(null); } } return $this; }
}