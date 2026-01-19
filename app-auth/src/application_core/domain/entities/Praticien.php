<?php
declare(strict_types=1);

namespace toubilib\core\domain\entities;

class Praticien
{
    public function __construct(
        private string $id,
        private string $nom,
        private string $prenom,
        private string $ville,
        private string $email,
        private string $specialite,
        private array $motifs,
    ) {
    }
    public function getId(): string
    {
        return $this->id;
    }
    public function getNom(): string
    {
        return $this->nom;
    }
    public function getPrenom(): string
    {
        return $this->prenom;
    }
    public function getVille(): string
    {
        return $this->ville;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getSpecialite(): string
    {
        return $this->specialite;
    }

    public function getMotifs(): array
    {
        return $this->motifs;
    }
}
