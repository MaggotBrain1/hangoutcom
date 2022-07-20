<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampusRepository::class)]
class Campus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'campus', targetEntity: User::class, orphanRemoval: true)]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'campusOrganizerSite', targetEntity: Hangout::class, orphanRemoval: true)]
    private Collection $hangouts;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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
    public function setId(int $id) : self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setCampus($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCampus() === $this) {
                $user->setCampus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Hangout>
     */
    public function getHangouts(): Collection
    {
        return $this->hangouts;
    }

    public function addHangout(Hangout $hangout): self
    {
        if (!$this->hangouts->contains($hangout)) {
            $this->hangouts[] = $hangout;
            $hangout->setCampusOrganizerSite($this);
        }

        return $this;
    }

    public function removeHangout(Hangout $hangout): self
    {
        if ($this->hangouts->removeElement($hangout)) {
            // set the owning side to null (unless already changed)
            if ($hangout->getCampusOrganizerSite() === $this) {
                $hangout->setCampusOrganizerSite(null);
            }
        }

        return $this;
    }
}
