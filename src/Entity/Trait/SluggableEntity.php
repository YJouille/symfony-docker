<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait SluggableEntity
{
    #[ORM\Column(length: 128, unique: true)]
    #[Gedmo\Slug(fields: ["title"])]
    private $slug;

    public function getSlug()
    {
        return $this->slug;
    }
}
