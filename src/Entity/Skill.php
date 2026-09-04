<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Un domaine de la matrice de compétences (niveau actuel vs cible).
 */
#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[ORM\Table(name: '`skill`')]
class Skill implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    #[Assert\NotBlank]
    private ?string $name = null;

    /**
     * Libellé court utilisé sur le radar (ex. « Recon. »).
     */
    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    private ?string $shortLabel = null;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 5)]
    private int $current = 0;

    #[ORM\Column]
    #[Assert\Range(min: 0, max: 5)]
    private int $target = 5;

    /**
     * Ordre d'affichage (le radar suit cet ordre).
     */
    #[ORM\Column]
    private int $position = 0;

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function isStrength(): bool
    {
        return $this->current >= 4;
    }

    public function isGap(): bool
    {
        return $this->current <= 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getShortLabel(): ?string
    {
        return $this->shortLabel;
    }

    public function setShortLabel(string $shortLabel): static
    {
        $this->shortLabel = $shortLabel;

        return $this;
    }

    public function getCurrent(): int
    {
        return $this->current;
    }

    public function setCurrent(int $current): static
    {
        $this->current = $current;

        return $this;
    }

    public function getTarget(): int
    {
        return $this->target;
    }

    public function setTarget(int $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
