<?php

namespace App\Entity;

use App\Enum\EntryStatus;
use App\Enum\EntryType;
use App\Repository\EntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EntryRepository::class)]
#[ORM\Table(name: '`entry`')]
#[ORM\HasLifecycleCallbacks]
class Entry implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank]
    private ?string $reference = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(length: 220, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 20, enumType: EntryType::class)]
    private EntryType $type = EntryType::LAB;

    #[ORM\Column(length: 20, enumType: EntryStatus::class)]
    private EntryStatus $status = EntryStatus::DRAFT;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $excerpt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objectif = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $protocole = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observations = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $retiens = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\Column(nullable: true)]
    private ?int $readingMinutes = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $terrain = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $outils = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $duree = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $prerequis = null;

    /**
     * Étiquette affichée dans le journal quand l'entrée n'est pas encore publiée
     * (ex. « Planifié · M2 », « En rédaction »).
     */
    #[ORM\Column(length: 40, nullable: true)]
    private ?string $planningLabel = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->reference ? sprintf('%s · %s', $this->reference, (string) $this->title) : (string) $this->title;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function refreshBeforeSave(): void
    {
        if (!$this->slug && $this->title) {
            $this->slug = (new AsciiSlugger())->slug($this->title)->lower()->toString();
        }
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isPublished(): bool
    {
        return EntryStatus::PUBLISHED === $this->status;
    }

    /**
     * Texte affiché dans la colonne « Statut » du journal.
     */
    public function statusLabel(): string
    {
        if ($this->isPublished() && $this->publishedAt) {
            return $this->publishedAt->format('d/m/Y');
        }

        return $this->planningLabel ?: $this->status->label();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getType(): EntryType
    {
        return $this->type;
    }

    public function setType(EntryType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): EntryStatus
    {
        return $this->status;
    }

    public function setStatus(EntryStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(string $excerpt): static
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getObjectif(): ?string
    {
        return $this->objectif;
    }

    public function setObjectif(?string $objectif): static
    {
        $this->objectif = $objectif;

        return $this;
    }

    public function getProtocole(): ?string
    {
        return $this->protocole;
    }

    public function setProtocole(?string $protocole): static
    {
        $this->protocole = $protocole;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): static
    {
        $this->observations = $observations;

        return $this;
    }

    public function getRetiens(): ?string
    {
        return $this->retiens;
    }

    public function setRetiens(?string $retiens): static
    {
        $this->retiens = $retiens;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    public function getReadingMinutes(): ?int
    {
        return $this->readingMinutes;
    }

    public function setReadingMinutes(?int $readingMinutes): static
    {
        $this->readingMinutes = $readingMinutes;

        return $this;
    }

    public function getTerrain(): ?string
    {
        return $this->terrain;
    }

    public function setTerrain(?string $terrain): static
    {
        $this->terrain = $terrain;

        return $this;
    }

    public function getOutils(): ?string
    {
        return $this->outils;
    }

    public function setOutils(?string $outils): static
    {
        $this->outils = $outils;

        return $this;
    }

    public function getDuree(): ?string
    {
        return $this->duree;
    }

    public function setDuree(?string $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getPrerequis(): ?string
    {
        return $this->prerequis;
    }

    public function setPrerequis(?string $prerequis): static
    {
        $this->prerequis = $prerequis;

        return $this;
    }

    public function getPlanningLabel(): ?string
    {
        return $this->planningLabel;
    }

    public function setPlanningLabel(?string $planningLabel): static
    {
        $this->planningLabel = $planningLabel;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
