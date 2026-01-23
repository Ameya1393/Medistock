<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'consumption')]
class Consumption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Drug::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Drug $drug;

    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $quantity;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $consumedAt;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    private string $loggedBy;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->consumedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDrug(): Drug
    {
        return $this->drug;
    }

    public function setDrug(Drug $drug): self
    {
        $this->drug = $drug;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getConsumedAt(): \DateTimeImmutable
    {
        return $this->consumedAt;
    }

    public function setConsumedAt(\DateTimeImmutable $consumedAt): self
    {
        $this->consumedAt = $consumedAt;
        return $this;
    }

    public function getLoggedBy(): string
    {
        return $this->loggedBy;
    }

    public function setLoggedBy(string $loggedBy): self
    {
        $this->loggedBy = $loggedBy;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }
}

