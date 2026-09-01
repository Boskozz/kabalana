<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Image',
                'mapped' => false, // Pour gérer l'upload
                'required' => false
            ])
            ->add('alt', TextType::class, [
                'label' => 'Texte alternatif',
                'required' => false
            ])
            ->add('legende', TextType::class, [
                'label' => 'Légende',
                'required' => false
            ]);
    }
}