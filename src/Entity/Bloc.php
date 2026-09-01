<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity]
#[ORM\Table(name: 'blocs')]
#[Vich\Uploadable]
class Bloc
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'blocs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Page $page = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    // CHANGEMENT ICI : type: 'text' au lieu de 'json'
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;  // Changé en ?string

    #[ORM\Column]
    private ?int $position = 0;

    #[ORM\Column(nullable: true)]
    private ?int $articleNumber = null;  // 👈 Numéro d'article (1, 2, 3...)

    #[ORM\Column(nullable: true)]
    private ?int $blocNumber = null;     // 👈 Numéro de bloc dans l'article (1, 2, 3...)

    #[ORM\Column(nullable: true)]
    private ?int $colonnes = 1;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cssClass = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $requiredRole = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $expiresAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $privateFilename = null;

    #[Vich\UploadableField(
        mapping: 'bloc_private_audio',
        fileNameProperty: 'privateFilename'
    )]
    private ?File $privateFile = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->isActive = true;
        $this->colonnes = 1;
    }

    // Getters et setters
    public function getId(): ?int { return $this->id; }

    public function getPage(): ?Page { return $this->page; }
    public function setPage(?Page $page): self { $this->page = $page; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }

public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

// Méthode utilitaire pour obtenir le contenu sous forme de tableau
    public function getContentAsArray(): array
    {
        if (empty($this->content)) {
            return [];
        }
        $data = json_decode($this->content, true);
        return is_array($data) ? $data : [];
    }

    // Méthode utilitaire pour définir le contenu depuis un tableau
    public function setContentFromArray(array $data): self
    {
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $this;
    }

    public function getPosition(): ?int { return $this->position; }
    public function setPosition(int $position): self { $this->position = $position; return $this; }

    public function getArticleNumber(): ?int { return $this->articleNumber; }
    public function setArticleNumber(?int $articleNumber): self { $this->articleNumber = $articleNumber; return $this; }

    public function getBlocNumber(): ?int { return $this->blocNumber; }
    public function setBlocNumber(?int $blocNumber): self { $this->blocNumber = $blocNumber; return $this; }

    // Méthode utilitaire pour afficher la position formatée
    public function getFormattedPosition(): string
    {
        if ($this->articleNumber !== null && $this->blocNumber !== null) {
            return $this->articleNumber . '-' . $this->blocNumber;
        }
        return (string) $this->position;
    }

    public function getColonnes(): ?int { return $this->colonnes; }
    public function setColonnes(?int $colonnes): self { $this->colonnes = $colonnes; return $this; }

    public function getCssClass(): ?string { return $this->cssClass; }
    public function setCssClass(?string $cssClass): self { $this->cssClass = $cssClass; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): self { $this->isActive = $isActive; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    public function getRequiredRole(): ?string { return $this->requiredRole; }
    public function setRequiredRole(?string $requiredRole): self
    {
        $this->requiredRole = ($requiredRole === null || $requiredRole === '') ? null : $requiredRole;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface { return $this->expiresAt; }
    public function setExpiresAt(?\DateTimeInterface $expiresAt): self { $this->expiresAt = $expiresAt; return $this; }

    public function getPrivateFilename(): ?string { return $this->privateFilename; }
    public function setPrivateFilename(?string $privateFilename): self { $this->privateFilename = $privateFilename; return $this; }

    public function getPrivateFile(): ?File { return $this->privateFile; }
    public function setPrivateFile(?File $privateFile = null): void
    {
        $this->privateFile = $privateFile;
        if (null !== $privateFile) {
            $this->updatedAt = new \DateTime();
        }
    }

    public function isRestricted(): bool
    {
        return $this->requiredRole !== null || $this->expiresAt !== null;
    }
}