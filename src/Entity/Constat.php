<?php

namespace App\Entity;

use App\Repository\ConstatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConstatRepository::class)]
class Constat implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $enonce = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $majeur = false;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $datePublication = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $retire = false;

    #[ORM\ManyToOne(inversedBy: 'constats')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Entry $entry = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getEnonce(): ?string
    {
        return $this->enonce;
    }

    public function setEnonce(?string $enonce): static
    {
        $this->enonce = $enonce;

        return $this;
    }

    public function isMajeur(): bool
    {
        return $this->majeur;
    }

    public function setMajeur(bool $majeur): static
    {
        $this->majeur = $majeur;

        return $this;
    }

    public function getDatePublication(): ?\DateTimeImmutable
    {
        return $this->datePublication;
    }

    public function setDatePublication(?\DateTimeImmutable $datePublication): static
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function isRetire(): bool
    {
        return $this->retire;
    }

    public function setRetire(bool $retire): static
    {
        $this->retire = $retire;

        return $this;
    }

    public function getEntry(): ?Entry
    {
        return $this->entry;
    }

    public function setEntry(?Entry $entry): static
    {
        $this->entry = $entry;

        return $this;
    }

    public function __toString(): string
    {
        return trim(sprintf('%s · %s', (string) $this->reference, (string) $this->enonce));
    }
}
