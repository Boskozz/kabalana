<?php

namespace App\Form;

use App\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    '🖼️ Image' => 'image',
                    '🎵 Audio' => 'audio',
                    '🎥 Vidéo' => 'video',
                    '📄 Document' => 'document',
                ],
                'label' => 'Type de média'
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier',
                'required' => true,
                'help' => 'Formats acceptés : JPG, PNG, GIF, WEBP, MP3, WAV, MP4, PDF...'
            ])
            ->add('legende', TextType::class, [
                'label' => 'Légende',
                'required' => false
            ])
            ->add('alt', TextType::class, [
                'label' => 'Texte alternatif (SEO)',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}