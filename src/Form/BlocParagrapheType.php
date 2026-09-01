<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BlocParagrapheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('texte', TextareaType::class, [
                'label' => 'Contenu du paragraphe',
                'attr' => ['rows' => 6]
            ])
            ->add('class', TextType::class, [
                'label' => 'Classes CSS (optionnel)',
                'required' => false
            ]);
    }
}