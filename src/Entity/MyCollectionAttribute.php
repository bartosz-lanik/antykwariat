<?php

namespace App\Entity;

use App\Repository\MyCollectionAttributeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MyCollectionAttributeRepository::class)
 */
class MyCollectionAttribute
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
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity=MyCollection::class, inversedBy="myCollectionAttributes")
     */
    private $MyCollection;

    /**
     * @ORM\ManyToOne(targetEntity=Attribute::class, inversedBy="myCollectionAttributes")
     */
    private $attribute;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getMyCollection(): ?MyCollection
    {
        return $this->MyCollection;
    }

    public function setMyCollection(?MyCollection $MyCollection): self
    {
        $this->MyCollection = $MyCollection;

        return $this;
    }

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(?Attribute $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }
}
