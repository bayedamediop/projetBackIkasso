<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ReservationsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;



/**
 * @ORM\Entity(repositoryClass=ReservationsRepository::class)
 * @ApiResource(
 *      collectionOperations={
 *     "get_reservation_un_usr"={
 *                  "method"="GET",
 *                    "path" = "/reservation/users",
 *                     "normalization_context"={"groups"={"getReservationdunUser:read"}},
 *              },
 *
 *          "get_reservationdunUser"={
 *              "route_name"="reservationdunUser",
 *            },
 *     "get_reservationdunUser"={
 *              "route_name"="reservationdunannuler",
 *            },
 *      },
 *    itemOperations={
 *      "get_reservation_by_id"={
 *                  "method"="GET",
 *                    "path" = "/admin/article/{id}",
 *                     "normalization_context"={"groups"={"articlesRead:read"}},
 *              },
 *
 *
 *      },
 * )
 *@ApiFilter(SearchFilter::class, properties={"validerservation" : "exact"})
 */
class Reservations
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups ({"getReservationdunUser"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Clients::class, inversedBy="reservations")
     *@Groups ({"getReservationdunUser"})
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity=Articles::class, inversedBy="reservations")
     *@Groups ({"getReservationdunUser"})
     */
    private $article;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     */
    private $dateReservation;

    /**
     * @ORM\Column(type="boolean")
     *@Groups ({"getReservationdunUser"})
     */
    private $validerRservation = false;

    /**
     * @ORM\Column(type="boolean")
     *@Groups ({"getReservationdunUser"})
     */
    private $annulerRservation = false;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reservations")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Clients
    {
        return $this->client;
    }

    public function setClient(?Clients $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getArticle(): ?Articles
    {
        return $this->article;
    }

    public function setArticle(?Articles $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->dateReservation;
    }

    public function setDateReservation(?\DateTimeInterface $dateReservation): self
    {
        $this->dateReservation = $dateReservation;

        return $this;
    }

    public function getValiderRservation(): ?bool
    {
        return $this->validerRservation;
    }

    public function setValiderRservation(bool $validerRservation): self
    {
        $this->validerRservation = $validerRservation;

        return $this;
    }

    public function getAnnulerRservation(): ?bool
    {
        return $this->annulerRservation;
    }

    public function setAnnulerRservation(bool $annulerRservation): self
    {
        $this->annulerRservation = $annulerRservation;

        return $this;
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
}
