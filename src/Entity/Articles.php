<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ArticlesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;


/**
 *
 * @ORM\Entity(repositoryClass=ArticlesRepository::class)
 *  @ApiResource(
 *     collectionOperations={
 *     "get_articles"={
 *                  "method"="GET",
 *                    "path" = "/admin/articles",
 *                     "normalization_context"={"groups"={"articlesRead:read"}},
 *              },
 *
 *
 *      },
 *     itemOperations={
 *   "get_article_by_id"={
 *                  "method"="GET",
 *                    "path" = "/admin/article/{id}",
 *                     "normalization_context"={"groups"={"articlesRead:read"}},
 *              },
 *
 *
 *      },
 *     )
 * @ApiFilter(BooleanFilter::class, properties={"archivage"})

 */
class Articles
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="articles")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups ({"articlesRead:read","getReservationdunUser"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups ({"articlesRead:read","getReservationdunUser"})
     */
    private $adresseArticle;

    /**
     * @ORM\Column(type="blob")
     * @Groups ({"articlesRead:read"})
     */
    private $image;

    /**
     * @ORM\Column(type="blob")
     * @Groups ({"articlesRead:read"})
     */
    private $image3D;

    /**
     * @ORM\Column(type="blob")
     * @Groups ({"articlesRead:read"})
     */
    private $video;

    /**
     * @ORM\Column(type="date")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups ({"articlesRead:read"})
     */
    private $prix;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $genre;

    /**
     * @ORM\Column(type="boolean")
     */
    private $archivage = true;

    /**
     * @ORM\OneToMany(targetEntity=Reservations::class, mappedBy="article")
     *
     */
    private $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAdresseArticle(): ?string
    {
        return $this->adresseArticle;
    }

    public function setAdresseArticle(string $adresseArticle): self
    {
        $this->adresseArticle = $adresseArticle;

        return $this;
    }

    public function getImage()
    {

        $image = $this->image;
        if ($image) {
            return (base64_encode(stream_get_contents($this->image)));
        }
        return $image;
    }

    public function setImage($image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImage3D()
    {

        $image3D = $this->image3D;
        if ($image3D) {
            return (base64_encode(stream_get_contents($this->image3D)));
        }
        return $image3D;
    }

    public function setImage3D($image3D): self
    {
        $this->image3D = $image3D;

        return $this;
    }

    public function getVideo()
    {

        $video = $this->video;
        if ($video) {
            return (base64_encode(stream_get_contents($this->video)));
        }
        return $video;
    }

    public function setVideo($video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getArchivage(): ?bool
    {
        return $this->archivage;
    }

    public function setArchivage(bool $archivage): self
    {
        $this->archivage = $archivage;

        return $this;
    }

    /**
     * @return Collection<int, Reservations>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservations $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setArticle($this);
        }

        return $this;
    }

    public function removeReservation(Reservations $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getArticle() === $this) {
                $reservation->setArticle(null);
            }
        }

        return $this;
    }
}
