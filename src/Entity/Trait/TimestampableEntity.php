<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Timestampable Trait, usable with PHP >= 5.4
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
trait TimestampableEntity
{
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTime $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTime $updatedAt = null;

    final public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    final public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    final public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    final public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
