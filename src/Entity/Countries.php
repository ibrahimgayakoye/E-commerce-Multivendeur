<?php

namespace App\Entity;

use App\Repository\CountriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CountriesRepository::class)]
class Countries
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message:'Le nom du produit ne peut etre vide')]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Subscription::class, inversedBy: 'countries')]
    private Collection $subscriptions;

    #[ORM\ManyToMany(targetEntity: WinningProducts::class, mappedBy: 'countries')]
    private Collection $winningProducts;

   

   
    public function __construct()
    {
        
        $this->subscriptions = new ArrayCollection();
        $this->winningProducts = new ArrayCollection();
  
    }



    public function __toString()
    {
        return $this->name;
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

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): static
    {
        $this->subscriptions->removeElement($subscription);

        return $this;
    }

    /**
     * @return Collection<int, WinningProducts>
     */
    public function getWinningProducts(): Collection
    {
        return $this->winningProducts;
    }

    public function addWinningProduct(WinningProducts $winningProduct): static
    {
        if (!$this->winningProducts->contains($winningProduct)) {
            $this->winningProducts->add($winningProduct);
            $winningProduct->addCountry($this);
        }

        return $this;
    }

    public function removeWinningProduct(WinningProducts $winningProduct): static
    {
        if ($this->winningProducts->removeElement($winningProduct)) {
            $winningProduct->removeCountry($this);
        }

        return $this;
    }

    
   

}
