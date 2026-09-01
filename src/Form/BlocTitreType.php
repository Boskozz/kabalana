<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BlocTitreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('niveau', ChoiceType::class, [
                'choices' => [
                    'H1' => 'h1',
                    'H2' => 'h2',
                    'H3' => 'h3',
                    'H4' => 'h4',
                ],
                'label' => 'Niveau de titre'
            ])
            ->add('texte', TextType::class, [
                'label' => 'Texte du titre'
            ])
            ->add('class', TextType::class, [
                'label' => 'Classes CSS (optionnel)',
                'required' => false
            ]);
    }
}