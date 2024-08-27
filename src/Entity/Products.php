<?php

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\SlugTrait;
use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;



#[ORM\Entity(repositoryClass: ProductsRepository::class)]

class Products
{
    use CreatedAtTrait;
    use SlugTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:'Le nom du produit ne peut etre vide')]
    #[Assert\Length(
         min: 5,
         minMessage: " Le titre doit faire au moins {{ limit }} caracteres",
         maxMessage: ' Le titre ne doit pas faire plus de {{ limit }} caractere'
    )]
    
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
   
    private ?int $price = null;

    #[ORM\Column]
    private ?int $stock = null;

   

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categories $categories = null;

    #[ORM\OneToMany(mappedBy: 'products', targetEntity: Images::class, orphanRemoval: true, cascade:['persist'])]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'products', targetEntity: OrderDetails::class)]
    private Collection $orderDetails;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Users $user = null;

   

    #[ORM\Column(nullable: true)]
    private ?bool $is_winning = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_send = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_rejected = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Statuts $statuts = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Videos::class,orphanRemoval: true, cascade:['persist'])]
    private Collection $videos;

    
    

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->orderDetails = new ArrayCollection();
        $this->created_at =  new \DateTimeImmutable();
        $this->videos = new ArrayCollection();
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

    

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

   

    public function getCategories(): ?Categories
    {
        return $this->categories;
    }

    public function setCategories(?Categories $categories): static
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return Collection<int, Images>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Images $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProducts($this);
        }

        return $this;
    }

    public function removeImage(Images $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProducts() === $this) {
                $image->setProducts(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderDetails>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): static
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->add($orderDetail);
            $orderDetail->setProducts($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): static
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getProducts() === $this) {
                $orderDetail->setProducts(null);
            }
        }

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;

        return $this;
    }

    
    public function isIsWinning(): ?bool
    {
        return $this->is_winning;
    }

    public function setIsWinning(?bool $is_winning): static
    {
        $this->is_winning = $is_winning;

        return $this;
    }

    public function isIsSend(): ?bool
    {
        return $this->is_send;
    }

    public function setIsSend(?bool $is_send): static
    {
        $this->is_send = $is_send;

        return $this;
    }

    public function isIsRejected(): ?bool
    {
        return $this->is_rejected;
    }

    public function setIsRejected(?bool $is_rejected): static
    {
        $this->is_rejected = $is_rejected;

        return $this;
    }

    public function getStatuts(): ?Statuts
    {
        return $this->statuts;
    }

    public function setStatuts(?Statuts $statuts): static
    {
        $this->statuts = $statuts;

        return $this;
    }

    /**
     * @return Collection<int, Videos>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Videos $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setProduct($this);
        }

        return $this;
    }

    public function removeVideo(Videos $video): static
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getProduct() === $this) {
                $video->setProduct(null);
            }
        }

        return $this;
    }

   
}
