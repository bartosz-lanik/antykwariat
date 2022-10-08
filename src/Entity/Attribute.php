<?php

namespace App\Entity;

use App\Repository\AttributeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AttributeRepository::class)
 */
class Attribute
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
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="attributes")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity=MyCollectionAttribute::class, mappedBy="attribute")
     */
    private $myCollectionAttributes;

    public function __construct()
    {
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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
            $myCollectionAttribute->setAttribute($this);
        }

        return $this;
    }

    public function removeMyCollectionAttribute(MyCollectionAttribute $myCollectionAttribute): self
    {
        if ($this->myCollectionAttributes->removeElement($myCollectionAttribute)) {
            // set the owning side to null (unless already changed)
            if ($myCollectionAttribute->getAttribute() === $this) {
                $myCollectionAttribute->setAttribute(null);
            }
        }

        return $this;
    }
}
