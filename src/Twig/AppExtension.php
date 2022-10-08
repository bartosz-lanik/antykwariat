<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('trimCat', [$this, 'trimCat'], ['is_safe' => ['html']]),
        ];
    }

    public function trimCat(string $text): string
    {
        $text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
        return trim(strtolower(trim(preg_replace('/[^a-z0-9]+/i', '', $text))), '-');
    }
}