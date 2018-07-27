<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EtablissementRepository")
 * @UniqueEntity(fields="username", message="Code déjà pris")
 */
class Etablissement implements UserInterface, \Serializable
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32, unique=true)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Kermesse", mappedBy="etablissement_id", orphanRemoval=true)
     */
    private $kermesses;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $admin;

    public function __construct()
    {
        $this->kermesses = new ArrayCollection();
        $this->admin = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->admin;
    }

    /**
     * @param bool $admin
     * @return Etablissement
     */
    public function setAdmin(bool $admin)
    {
        $this->admin = $admin;
        return $this;
    }

    /**
     * @return Collection|Kermesse[]
     */
    public function getKermesses(): Collection
    {
        return $this->kermesses;
    }

    public function addKermess(Kermesse $kermess): self
    {
        if (!$this->kermesses->contains($kermess)) {
            $this->kermesses[] = $kermess;
            $kermess->setEtablissementId($this);
        }

        return $this;
    }

    public function removeKermess(Kermesse $kermess): self
    {
        if ($this->kermesses->contains($kermess)) {
            $this->kermesses->removeElement($kermess);
            // set the owning side to null (unless already changed)
            if ($kermess->getEtablissementId() === $this) {
                $kermess->setEtablissementId(null);
            }
        }

        return $this;
    }

    /**
     * Returns the roles granted to the user.
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return array The user roles
     */
    public function getRoles()
    {
        $roles = ['ROLE_USER'];
        if ($this->isAdmin()) {
            $roles[] = 'ROLE_ADMIN';
        }
        return $roles;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        return;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([$this->id, $this->username, $this->password]);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        [$this->id, $this->username, $this->password] = unserialize($serialized, ['allowed_classes' => false]);
    }
}
