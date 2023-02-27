<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait ArchivableEntity
{
    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTime $archivedAt = null;

    final public function getArchivedAt(): ?\DateTimeInterface
    {
        return $this->archivedAt;
    }

    final public function setArchivedAt(?\DateTimeInterface $archivedAt): self
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    final public function isArchived(): bool
    {
        return $this->getArchivedAt() !== null;
    }
}
