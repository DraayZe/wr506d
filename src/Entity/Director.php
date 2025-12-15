<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DirectorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DirectorRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['director:read']],
    denormalizationContext: ['groups' => ['director:write']]
)]
/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Director
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['director:read', 'movie:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['director:read', 'director:write', 'movie:read'])]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['director:read', 'director:write', 'movie:read'])]
    private ?string $firstname = null;

    #[ORM\Column]
    #[Groups(['director:read', 'director:write', 'movie:read'])]
    private ?\DateTime $dob = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['director:read', 'director:write', 'movie:read'])]
    private ?\DateTime $dod = null;

    /**
     * @var Collection<int, Movie>
     */
    #[ORM\OneToMany(targetEntity: Movie::class, mappedBy: 'director')]
    #[Groups(['director:read'])]
    private Collection $movies;

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

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getDob(): ?\DateTime
    {
        return $this->dob;
    }

    public function setDob(\DateTime $dob): static
    {
        $this->dob = $dob;

        return $this;
    }

    public function getDod(): ?\DateTime
    {
        return $this->dod;
    }

    public function setDod(?\DateTime $dod): static
    {
        $this->dod = $dod;

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
            $movie->setDirector($this);
        }

        return $this;
    }

    public function removeMovie(Movie $movie): static
    {
        if ($this->movies->removeElement($movie)) {
            // set the owning side to null (unless already changed)
            if ($movie->getDirector() === $this) {
                $movie->setDirector(null);
            }
        }

        return $this;
    }
}
