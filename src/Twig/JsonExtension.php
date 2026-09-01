<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class JsonExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('json_decode', [$this, 'jsonDecode']),
        ];
    }

    public function jsonDecode(?string $value): array
    {
        if (empty($value)) {
            return [];
        }
        
        $data = json_decode($value, true);
        return is_array($data) ? $data : [];
    }
}