<?php

namespace App\Entity;

use App\Constraints as MyAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EtablissementRepository")
 * @UniqueEntity(fields="username", message="Code déjà pris")
 */
class Etablissement implements UserInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32, unique=true)
     * @MyAssert\Code
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
     * @ORM\OneToMany(targetEntity="App\Entity\Kermesse", mappedBy="etablissement", orphanRemoval=true)
     */
    private $kermesses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Membre", mappedBy="etablissement", orphanRemoval=true)
     */
    private $membres;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $admin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\OneToOne(targetEntity=Inscription::class, cascade={"persist", "remove"})
     */
    private $originInscription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetPwdKey;

    /**
     * @ORM\OneToMany(targetEntity=TypeActivite::class, mappedBy="etablissement")
     */
    private $typeActivites;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="etablissement")
     */
    private $documents;

    public function __construct()
    {
        $this->kermesses = new ArrayCollection();
        $this->membres = new ArrayCollection();
        $this->admin = false;
        $this->typeActivites = new ArrayCollection();
        $this->documents = new ArrayCollection();
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
            $kermess->setEtablissement($this);
        }

        return $this;
    }

    public function removeKermess(Kermesse $kermess): self
    {
        if ($this->kermesses->contains($kermess)) {
            $this->kermesses->removeElement($kermess);
            // set the owning side to null (unless already changed)
            if ($kermess->getEtablissement() === $this) {
                $kermess->setEtablissement(null);
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
     * @return array the string representation of the object or null
     * @since 5.1.0
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param array $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function __unserialize(array $serialized): void
    {
        $this->id = $serialized['id'];
        $this->username = $serialized['username'];
        $this->password = $serialized['password'];
    }

    /**
     * @return Collection|Membre[]
     */
    public function getMembres(): Collection
    {
        return $this->membres;
    }

    public function addMembre(Membre $membre): self
    {
        if (!$this->membres->contains($membre)) {
            $this->membres[] = $membre;
            $membre->setEtablissement($this);
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getOriginInscription(): ?Inscription
    {
        return $this->originInscription;
    }

    public function setOriginInscription(?Inscription $originInscription): self
    {
        $this->originInscription = $originInscription;

        return $this;
    }

    public function getResetPwdKey(): ?string
    {
        return $this->resetPwdKey;
    }

    public function setResetPwdKey(?string $resetPwdKey): self
    {
        $this->resetPwdKey = $resetPwdKey;

        return $this;
    }

    /**
     * @return Collection<int, TypeActivite>
     */
    public function getTypeActivites(): Collection
    {
        return $this->typeActivites;
    }

    public function addTypeActivite(TypeActivite $typeActivite): self
    {
        if (!$this->typeActivites->contains($typeActivite)) {
            $this->typeActivites[] = $typeActivite;
            $typeActivite->setEtablissement($this);
        }

        return $this;
    }

    public function removeTypeActivite(TypeActivite $typeActivite): self
    {
        if ($this->typeActivites->removeElement($typeActivite)) {
            // set the owning side to null (unless already changed)
            if ($typeActivite->getEtablissement() === $this) {
                $typeActivite->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setEtablissement($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getEtablissement() === $this) {
                $document->setEtablissement(null);
            }
        }

        return $this;
    }
}
