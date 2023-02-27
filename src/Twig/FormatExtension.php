<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormatExtension extends AbstractExtension
{
    public function __construct(
    )
    {
    }

    final public function getFunctions(): array
    {
        return [
            new TwigFunction("formatPlural", [$this, "formatPlural"]),
            new TwigFunction("boolToString", [$this, "boolToString"])
        ];
    }
    final public function formatPlural(?int $number = null): string
    {
        return $number && $number >= 2 ? "s" : "";
    }

    final public function boolToString(?bool $parameter): string
    {
        return $parameter === true ? "Oui" : "Non";
    }
}

