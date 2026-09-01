<?php

namespace App\Controller\Admin;

use App\Entity\Bloc;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Vich\UploaderBundle\Form\Type\VichFileType;

class BlocCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Bloc::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort([
                'articleNumber' => 'ASC',
                'blocNumber' => 'ASC',
                'position' => 'ASC',
            ])
            ->setEntityLabelInSingular('Bloc')
            ->setEntityLabelInPlural('Blocs');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('page', 'Page'))
            ->add(BooleanFilter::new('isActive', 'Actif'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('page', 'Page'),
            ChoiceField::new('type', 'Type de bloc')
                ->setChoices([
                    '📝 Titre' => 'titre',
                    '📄 Paragraphe' => 'paragraphe',
                    '🖼️ Images' => 'images_groupe',
                    '🔗 Liens' => 'liens',
                    '🎥 Vidéo' => 'video',
                    '🎵 Audio' => 'audio',  // 👈 Nouveau
                    '📑 Document' => 'document', // 👈 Nouveau
                ]),
            CodeEditorField::new('content', 'Contenu (JSON)')
                ->setLanguage('yaml')
                ->setHelp('
        <strong>Format JSON selon le type de bloc :</strong><br>

        <strong>Titre :</strong><br>
        <code>{"niveau":"h1","texte":"Votre titre","class":"text-center"}</code><br>
        
        <strong>Paragraphe :</strong><br>
        <code>{"texte":"Votre contenu","class":"text-justify"}</code><br>
        
        <strong>Image :</strong><br>
        <code>{"images": [ {"url": "/uploads/image/nomfichier.jpg", "alt": "Description de l\'image", "legende": "Légende de l\'image" } ], "disposition": "horizontal"}</code><br>

        <strong>Liens :</strong><br>
        <em>Un seul lien :</em><br>
        <code>{"url":"https://www.exemple.com","texte":"Visiter le site","target":"_blank","class":"btn btn-primary"}</code><br>
        <em>Plusieurs liens :</em><br>
        <code>{"liens":[{"url":"https://www.exemple.com","texte":"Visiter le site","target":"_blank","class":"btn btn-primary"},{"url":"/contact","texte":"Me contacter","icone":"envelope"}]}</code><br>

        <strong>Vidéo YouTube :</strong><br>
        <code>{"url":"https://www.youtube.com/embed/IDENTIFIANT","class":"ratio ratio-16x9"}</code><br>

        <strong>Audio</strong><br>
        <code>{"file": "/uploads/audio/interview.mp3", "title": "Mon interview radio", "legende": "Émission du 15 juin 2026", "transcription": "Bonjour, je suis Marion...", "autoplay": false, "loop": false}</code><br>

        <strong>Document</strong><br>
        <code>{ "file": "/uploads/documents/brochure.pdf", "title": "Brochure de présentation", "legende": "Téléchargez notre brochure", "icone": "file-pdf"}</code><br>
        <br>
        <strong>🎨 Classes faciles (français) :</strong> à écrire dans le champ <strong>« CSS Classes »</strong> du bloc (sauf précision)<br>
        <em>Image :</em> <code>img-gauche</code>, <code>img-droite</code>, <code>img-centre</code>, <code>img-meme-espace</code>, <code>img-arrondie</code>, <code>img-ombre</code><br>
        <em>Lien :</em> <code>lien-gauche</code>, <code>lien-centre</code>, <code>lien-droite</code><br>
        <em>Bouton coloré :</em> <code>btn-bleu</code>, <code>btn-vert</code>, <code>btn-rouge</code>, <code>btn-jaune</code>, <code>btn-gris</code>, <code>btn-cyan</code><br>
        <em>Texte :</em> <code>texte-gauche</code>, <code>texte-centre</code>, <code>texte-droite</code>, <code>texte-justifie</code>, <code>texte-grand</code><br>
        <em>Espace :</em> <code>marge-haut</code>, <code>marge-bas</code>, <code>marge-auto</code>, <code>espace-tout</code>, <code>largeur-50</code>…

    ')
                ->formatValue(function ($value) {
                    // Si le contenu est un tableau (ancienne version), le convertir en JSON
                    if (is_array($value)) {
                        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }
                    return $value ?? '{}';
                }),
            NumberField::new('position', 'Position globale'),
            NumberField::new('articleNumber', 'Article #'),
            NumberField::new('blocNumber', 'Bloc #'),
            ChoiceField::new('colonnes', 'Colonnes')
                ->setChoices([
                    '1 colonne' => 1,
                    '2 colonnes' => 2,
                    '3 colonnes' => 3,
                    '4 colonnes' => 4
                ]),
            TextField::new('cssClass', 'CSS Classes')->setRequired(false),
            BooleanField::new('isActive', 'Actif'),
            DateTimeField::new('createdAt', 'Créé le')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Modifié le')->hideOnForm(),

            FormField::addFieldset('🔒 Accès restreint'),
            ChoiceField::new('requiredRole', 'Accès')
                ->setChoices([
                    '🔒 Réservé aux invités (ROLE_PARTAGE)' => 'ROLE_PARTAGE',
                ])
                ->setRequired(false)
                ->setFormTypeOption('placeholder', '🔓 Public (tout le monde)')
                ->renderAsBadges()
                ->setHelp('Choisissez "Réservé aux invités" pour que seuls les comptes avec le rôle ROLE_PARTAGE voient le bloc.'),
            DateTimeField::new('expiresAt', 'Visible jusqu\'au')
                ->setRequired(false)
                ->setHelp('Après cette date, le bloc sera masqué pour tout le monde. Laissez vide pour aucune limite.'),
            Field::new('privateFile', 'Fichier audio privé')
                ->setFormType(VichFileType::class)
                ->setHelp('Uniquement pour les blocs audio restreints. Le fichier est stocké hors du dossier public (URL non accessible).')
                ->setRequired(false),
        ];
    }
}
