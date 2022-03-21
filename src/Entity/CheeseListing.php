<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\CheeseListingRepository;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

#[ORM\Entity(repositoryClass: CheeseListingRepository::class)]
#[ApiResource(
    collectionOperations: ['get', 'post'],
    itemOperations: [
        'get',
        'put'
    ],
    shortName: 'cheeses',
    attributes: [
        'pagination_items_per_page' => 5,
        'formats' => ['jsonld', 'json', 'html', 'jsonhal', 'csv' => 'text/csv']
    ],
    denormalizationContext: ['groups' => ['cheese_listings:write']],
    normalizationContext: ['groups' => ['cheese_listings:read']]
)]
#[ApiFilter(BooleanFilter::class, properties: ['isPublished'])]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial'])]
#[ApiFilter(RangeFilter::class, properties: ['price'])]
#[ApiFilter(PropertyFilter::class)]
class CheeseListing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['cheese_listings:read', 'cheese_listings:write', 'user:read'])]
    #[NotBlank]
    #[Length(['min' => 2, 'max' => 50, 'maxMessage' => 'Describe your cheese in 55 chars or less'])]
    private ?string $title;

    #[ORM\Column(type: 'text')]
    #[Groups(['cheese_listings:read'])]
    #[NotBlank]
    private ?string $description;

    #[ORM\Column(type: 'integer')]
    #[Groups(['cheese_listings:read', 'cheese_listings:write', 'user:read'])]
    #[NotBlank]
    private ?int $price;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'boolean')]
    private bool $isPublished = false;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cheeseListings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cheese_listings:read', 'cheese_listings:write'])]
    #[Valid]
    private ?User $owner;

    public function __construct(string $title = null)
    {
        $this->title = $title;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    #[Groups('cheese_listings:read')]
    public function getShortDescription(): ?string
    {
        if (strlen($this->description) < 40) {
            return $this->description;
        }

        return substr($this->description, 0, 40).'...';
    }

    #[Groups(['cheese_listings:write'])]
    #[SerializedName('description')]
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * A good format to date
     */
    #[Groups('cheese_listings:read')]
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
