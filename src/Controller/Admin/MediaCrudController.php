<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichFileType;


class MediaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Media::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            // Afficher un aperçu de l'image si c'en est une
            // Pour les images (aperçu automatique)
            // Aperçu de l'image
            ImageField::new('relativePath', 'Aperçu')
                ->setBasePath('/uploads/')
                ->setUploadDir('public/uploads/')
                ->hideOnForm(),

            ChoiceField::new('type', 'Type')
                ->setChoices([
                    '🖼️ Image' => 'image',
                    '🎵 Audio' => 'audio',
                    '🎥 Vidéo' => 'video',
                    '📄 Document' => 'document',
                ]),

            // 👇 Champ d'upload personnalisé
            Field::new('file', 'Fichier')
                ->setFormType(VichFileType::class)
                ->setHelp('Formats : JPG, PNG, GIF, WEBP, MP3, WAV, MP4, PDF... (max 50M)')
                ->onlyOnForms(),

            TextField::new('filename', 'Nom du fichier')
                ->setTemplatePath('admin/field/copy_filename.html.twig')
                ->hideOnForm(),

            TextField::new('originalName', 'Nom original')
                ->hideOnForm(),

            TextField::new('legende', 'Légende')
                ->setRequired(false),

            TextField::new('alt', 'Texte alternatif (SEO)')
                ->setRequired(false)
                ->setHelp('Pour les images uniquement'),

            NumberField::new('size', 'Taille')
                ->setTemplatePath('admin/field/file_size.html.twig')
                ->formatValue(function ($value) {
                    // 👇 Pour debug : voir ce qui arrive
                    // dd($value);
                    return $value;
                })
                ->hideOnForm(),

            DateTimeField::new('createdAt', 'Créé le')
                ->hideOnForm(),

            DateTimeField::new('updatedAt', 'Modifié le')
                ->hideOnForm(),
        ];
    }
}
