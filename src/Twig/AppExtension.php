<?php

namespace App\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction("getParameter", [$this, "getParameter"]),
            new TwigFunction("get_env", [$this, "getEnvironmentVariable"]),
        ];
    }

    public function getParameter(string $param): array|bool|string|int|float|null
    {
        return $this->parameterBag->get($param);
    }

    public function getEnvironmentVariable($varname)
    {
        return $_ENV[$varname] ?? $_SERVER[$varname] ?? null;
    }
}
