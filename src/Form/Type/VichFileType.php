<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType as BaseVichFileType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VichFileType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_delete' => true,
            'download_uri' => true,
            'download_label' => 'Télécharger',
            'download_label_translation_domain' => 'messages',
        ]);
    }

    public function getParent(): string
    {
        return BaseVichFileType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'app_vich_file';
    }
}