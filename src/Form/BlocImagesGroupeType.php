<?php

namespace App\Form;

use App\Form\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BlocImagesGroupeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('images', CollectionType::class, [
                'entry_type' => ImageType::class, // Un formulaire pour une image
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'label' => 'Images'
            ])
            ->add('disposition', ChoiceType::class, [
                'choices' => [
                    'Horizontal' => 'horizontal',
                    'Grille' => 'grille'
                ],
                'label' => 'Disposition des images'
            ])
            ->add('class', TextType::class, [
                'label' => 'Classes CSS (optionnel)',
                'required' => false
            ]);
    }
}