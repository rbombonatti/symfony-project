<?php

namespace App\Entity;

use App\Repository\HashesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HashesRepository::class)]
class Hashes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateTimeBatch = null;

    #[ORM\Column]
    private ?int $blockNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $entryString = null;

    #[ORM\Column(length: 255)]
    private ?string $generatedKey = null;

    #[ORM\Column(length: 255)]
    private ?string $generatedHash = null;

    #[ORM\Column]
    private ?int $generationAttempts = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateTimeBatch(): ?\DateTimeInterface
    {
        return $this->dateTimeBatch;
    }

    public function setDateTimeBatch(\DateTimeInterface $dateTimeBatch): self
    {
        $this->dateTimeBatch = $dateTimeBatch;

        return $this;
    }

    public function getBlockNumber(): ?int
    {
        return $this->blockNumber;
    }

    public function setBlockNumber(int $blockNumber): self
    {
        $this->blockNumber = $blockNumber;
        return $this;
    }

    public function getEntryString(): ?string
    {
        return $this->entryString;
    }

    public function setEntryString(string $entryString): self
    {
        $this->entryString = $entryString;
        return $this;
    }

    public function getGeneratedKey(): ?string
    {
        return $this->generatedKey;
    }

    public function setGeneratedKey(string $generatedKey): self
    {
        $this->generatedKey = $generatedKey;
        return $this;
    }

    public function getGeneratedHash(): ?string
    {
        return $this->generatedHash;
    }

    public function setGeneratedHash(string $generatedHash): self
    {
        $this->generatedHash = $generatedHash;
        return $this;
    }

    public function getGenerationAttempts(): ?int
    {
        return $this->generationAttempts;
    }

    public function setGenerationAttempts(int $generationAttempts): self
    {
        $this->generationAttempts = $generationAttempts;
        return $this;
    }

}
