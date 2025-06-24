<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deliveryAt = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column]
    private ?float $totalPrice = null;

    #[ORM\Column(length: 50)]
    private ?string $paymentProvider = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Users $users = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Addresses $addresses = null;

    /**
     * @var Collection<int, OrdersProducts>
     */
    #[ORM\OneToMany(targetEntity: OrdersProducts::class, mappedBy: 'orders')]
    private Collection $ordersProducts;

    public function __construct()
    {
        $this->ordersProducts = new ArrayCollection();
    }

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDeliveryAt(): ?\DateTimeImmutable
    {
        return $this->deliveryAt;
    }

    public function setDeliveryAt(?\DateTimeImmutable $deliveryAt): static
    {
        $this->deliveryAt = $deliveryAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getPaymentProvider(): ?string
    {
        return $this->paymentProvider;
    }

    public function setPaymentProvider(string $paymentProvider): static
    {
        $this->paymentProvider = $paymentProvider;

        return $this;
    }

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): static
    {
        $this->users = $users;

        return $this;
    }

    public function getAddresses(): ?Addresses
    {
        return $this->addresses;
    }

    public function setAddresses(?Addresses $addresses): static
    {
        $this->addresses = $addresses;

        return $this;
    }

    /**
     * @return Collection<int, OrdersProducts>
     */
    public function getOrdersProducts(): Collection
    {
        return $this->ordersProducts;
    }

    public function addOrdersProduct(OrdersProducts $ordersProduct): static
    {
        if (!$this->ordersProducts->contains($ordersProduct)) {
            $this->ordersProducts->add($ordersProduct);
            $ordersProduct->setOrders($this);
        }

        return $this;
    }

    public function removeOrdersProduct(OrdersProducts $ordersProduct): static
    {
        if ($this->ordersProducts->removeElement($ordersProduct)) {
            // set the owning side to null (unless already changed)
            if ($ordersProduct->getOrders() === $this) {
                $ordersProduct->setOrders(null);
            }
        }

        return $this;
    }

    
    

   
}
