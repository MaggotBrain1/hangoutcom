<?php

namespace App\Entity;

use App\Repository\HangoutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HangoutRepository::class)]
class Hangout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $registerDateLimit = null;

    #[ORM\Column]
    private ?int $MaxOfRegistration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hangoutInformations = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'hangout')]
    private Collection $hangouts;

    #[ORM\ManyToOne(inversedBy: 'hangouts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $Status = null;

    #[ORM\ManyToOne(inversedBy: 'hangouts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    #[ORM\ManyToOne(inversedBy: 'hangouts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campusOrganizerSite = null;

    #[ORM\ManyToOne(inversedBy: 'hangouts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Place $place = null;

    public function __construct()
    {
        $this->hangouts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getDuration(): ?\DateTimeInterface
    {
        return $this->duration;
    }

    public function setDuration(\DateTimeInterface $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getRegisterDateLimit(): ?\DateTimeInterface
    {
        return $this->registerDateLimit;
    }

    public function setRegisterDateLimit(\DateTimeInterface $registerDateLimit): self
    {
        $this->registerDateLimit = $registerDateLimit;

        return $this;
    }

    public function getMaxOfRegistration(): ?int
    {
        return $this->MaxOfRegistration;
    }

    public function setMaxOfRegistration(int $MaxOfRegistration): self
    {
        $this->MaxOfRegistration = $MaxOfRegistration;

        return $this;
    }

    public function getHangoutInformations(): ?string
    {
        return $this->hangoutInformations;
    }

    public function setHangoutInformations(?string $hangoutInformations): self
    {
        $this->hangoutInformations = $hangoutInformations;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getHangouts(): Collection
    {
        return $this->hangouts;
    }

    public function addHangout(User $hangout): self
    {
        if (!$this->hangouts->contains($hangout)) {
            $this->hangouts[] = $hangout;
            $hangout->addHangout($this);
        }

        return $this;
    }

    public function removeHangout(User $hangout): self
    {
        if ($this->hangouts->removeElement($hangout)) {
            $hangout->removeHangout($this);
        }

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->Status;
    }

    public function setStatus(?Status $Status): self
    {
        $this->Status = $Status;

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): self
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getCampusOrganizerSite(): ?Campus
    {
        return $this->campusOrganizerSite;
    }

    public function setCampusOrganizerSite(?Campus $campusOrganizerSite): self
    {
        $this->campusOrganizerSite = $campusOrganizerSite;

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

        return $this;
    }
}
