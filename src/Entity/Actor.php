<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

use App\Repository\ActorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActorRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ['lastname' => 'start', 'firstname' => 'start', 'movies' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['dob'])]
#[ApiResource(
    normalizationContext: ['groups' => ['actor:read', 'movie:list', 'media_object:read']],
    denormalizationContext: ['groups' => ['actor:write']],
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Delete(
            normalizationContext: ['groups' => ['actor:delete']]
        )
    ]
)]

#[ORM\HasLifecycleCallbacks]
/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Actor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['actor:read', 'actor:list', 'actor:delete'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['actor:read', 'actor:list', 'actor:write'])]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['actor:read', 'actor:list', 'actor:write'])]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['actor:read', 'actor:write'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private ?\DateTimeImmutable $dob = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['actor:read', 'actor:write'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private ?\DateTimeImmutable $dod = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['actor:read', 'actor:write'])]
    private ?string $bio = null;

    /**
     * @var Collection<int, Movie>
     */
    #[ORM\ManyToMany(targetEntity: Movie::class, inversedBy: 'actors')]
    #[Groups(['actor:read', 'actor:write'])]
    private Collection $movies;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['actor:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'actors')]
    #[Groups(['actor:read', 'actor:list'])]
    private ?MediaObject $photo = null;

    /**
     * Nom complet (virtuel)
     */
    #[Groups(['actor:list', 'actor:read'])]
    public function getFullName(): string
    {
        return trim($this->lastname . ' ' . $this->firstname);
    }


    /**
     * Âge calculé (virtuel)
     */
    #[Groups(['actor:list', 'actor:read'])]
    public function getAge(): ?int
    {
        if ($this->dob === null) {
            return null;
        }
        // Si décédé, calcule l'âge au moment du décès
        $reference = $this->dod ?? new \DateTime();

        return $this->dob->diff($reference)->y;
    }

    public function __construct()
    {
        $this->movies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getDob(): ?\DateTimeImmutable
    {
        return $this->dob;
    }


    public function setDob(?\DateTimeImmutable $dob): static
    {
        $this->dob = $dob;
        return $this;
    }

    public function getDod(): ?\DateTimeImmutable
    {
        return $this->dod;
    }

    public function setDod(?\DateTimeImmutable $dod): static
    {
        $this->dod = $dod;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    /**
     * @return Collection<int, Movie>
     */
    public function getMovies(): Collection
    {
        return $this->movies;
    }

    public function addMovie(Movie $movie): static
    {
        if (!$this->movies->contains($movie)) {
            $this->movies->add($movie);
        }

        return $this;
    }

    public function removeMovie(Movie $movie): static
    {
        $this->movies->removeElement($movie);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new DateTimeImmutable();
        }
    }

    #[ORM\PreUpdate]
    public function ensureCreatedAtNotNull(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new DateTimeImmutable();
        }
    }

    public function getPhoto(): ?MediaObject
    {
        return $this->photo;
    }

    public function setPhoto(?MediaObject $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

}
