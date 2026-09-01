<?php

namespace App\Form\DataTransformer\JsonToArray;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class JsonToArrayTransformer implements DataTransformerInterface
{
    public function transform($value): string
    {
        // Transforme le tableau en JSON pour l'affichage dans le formulaire
        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        
        return $value ?? '{}';
    }

    public function reverseTransform($value): array
    {
        // Transforme le JSON en tableau pour le stockage en base de données
        if (empty($value)) {
            return [];
        }

        $data = json_decode($value, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransformationFailedException('Le JSON est invalide : ' . json_last_error_msg());
        }

        return $data;
    }
}