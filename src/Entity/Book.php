<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $author = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $summary = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $publication = null;

    #[ORM\Column]
    private ?bool $availability = null;

    #[ORM\ManyToOne(inversedBy: 'books')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'Books')]
    private ?User $borrower = null;

    #[ORM\ManyToOne(inversedBy: 'Lbooks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Librairy $librairy = null;

    #[ORM\ManyToOne(inversedBy: 'books')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Librairy $librairy1 = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function getPublication(): ?\DateTimeInterface
    {
        return $this->publication;
    }

    public function setPublication(\DateTimeInterface $publication): static
    {
        $this->publication = $publication;

        return $this;
    }

    public function isAvailability(): ?bool
    {
        return $this->availability;
    }

    public function setAvailability(bool $availability): static
    {
        $this->availability = $availability;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getBorrower(): ?User
    {
        return $this->borrower;
    }

    public function setBorrower(?User $borrower): static
    {
        $this->borrower = $borrower;

        return $this;
    }

    public function getLibrairy(): ?Librairy
    {
        return $this->librairy;
    }

    public function setLibrairy(?Librairy $librairy): static
    {
        $this->librairy = $librairy;

        return $this;
    }

    public function getLibrairy1(): ?Librairy
    {
        return $this->librairy1;
    }

    public function setLibrairy1(?Librairy $librairy1): static
    {
        $this->librairy1 = $librairy1;

        return $this;
    }
}
