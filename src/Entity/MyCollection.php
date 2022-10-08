<?php

namespace App\Entity;

use App\Repository\MyCollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MyCollectionRepository::class)
 */
class MyCollection
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="myCollections")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity=MyCollectionAttribute::class, mappedBy="MyCollection")
     */
    private $myCollectionAttributes;

    public function __construct()
    {
        $this->attributeObjects = new ArrayCollection();
        $this->myCollectionAttributes = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|MyCollectionAttribute[]
     */
    public function getMyCollectionAttributes(): Collection
    {
        return $this->myCollectionAttributes;
    }

    public function addMyCollectionAttribute(MyCollectionAttribute $myCollectionAttribute): self
    {
        if (!$this->myCollectionAttributes->contains($myCollectionAttribute)) {
            $this->myCollectionAttributes[] = $myCollectionAttribute;
            $myCollectionAttribute->setMyCollection($this);
        }

        return $this;
    }

    public function removeMyCollectionAttribute(MyCollectionAttribute $myCollectionAttribute): self
    {
        if ($this->myCollectionAttributes->removeElement($myCollectionAttribute)) {
            // set the owning side to null (unless already changed)
            if ($myCollectionAttribute->getMyCollection() === $this) {
                $myCollectionAttribute->setMyCollection(null);
            }
        }

        return $this;
    }
}
