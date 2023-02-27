<?php

namespace App\Entity\Trait;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Blameable Trait, usable with PHP >= 5.4
 *
 * @author David Buchmann <mail@davidbu.ch>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
trait BlameableEntity
{
    #[Gedmo\Blameable(on: 'create')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: "EAGER")]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?User $createdBy = null;

    #[Gedmo\Blameable(on: 'update')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?User $updatedBy = null;

    final public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    final public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    final public function setUpdatedBy(?User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    final public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }
}
