<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class JsonDecode extends AbstractExtension
{
    public function getFunctions(): Array
    {
        return [
            new TwigFunction("jsonDecode", [$this, "jsonDecode"])
        ];
    }

    public function jsonDecode(string $json): array
    {
        return json_decode($json);
    }
}
