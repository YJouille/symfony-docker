<?php

namespace App\Service\VichCustomNamer;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\Polyfill\FileExtensionTrait;

class WebpNamer implements NamerInterface
{
    use FileExtensionTrait;

    public function name($object, PropertyMapping $mapping): string
    {
        $name = \str_replace('.', '', \uniqid('', true));
        $extension = "webp";

        if (\is_string($extension) && '' !== $extension) {
            $name = \sprintf('%s.%s', $name, $extension);
        }

        return $name;
    }
}
