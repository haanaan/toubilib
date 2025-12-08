<?php
declare(strict_types=1);

namespace toubilib\core\domain\entities;

class Specialite
{
    public function __construct(
        private int $id,
        private string $libelle
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getLibelle(): string
    {
        return $this->libelle;
    }
}
