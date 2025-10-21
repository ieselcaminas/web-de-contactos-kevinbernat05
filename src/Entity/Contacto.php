<?php

namespace App\Entity;

use App\Repository\ContactoRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactoRepository::class)]
class Contacto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $telefono = null;

    #[ORM\Column(type: "string",length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email(message: "El email {{ value }} no es valido")]
    private $email = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    
    private ?Provincia $provincia = null;

    // Getter y setter para id (no es necesario el setter setId)
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): self 
    {
        $this->telefono = $telefono;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getProvincia(): ?Provincia
    {
        return $this->provincia;
    }

    public function setProvincia(?Provincia $provincia): self
    {
        $this->provincia = $provincia;
        return $this;
    }
}


