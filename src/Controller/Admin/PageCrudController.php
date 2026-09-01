<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use App\Repository\PageRepository;

class PageCrudController extends AbstractCrudController
{
    public function __construct(
        private PageRepository $pageRepository
    ) {}


    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $pages = $this->pageRepository->findBy([], ['section' => 'ASC', 'position' => 'ASC']);

        $current = $this->getContext()?->getEntity()->getInstance();
        $currentId = ($current instanceof Page) ? $current->getId() : null;

        $parentChoices = [];
        foreach ($pages as $p) {
            if ($p->getId() === $currentId) {
                continue;
            }
            $parentChoices[$p->getSection() . ' — ' . $p->getTitle() . ' (' . $p->getSlug() . ')'] = $p->getId();
        }

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titre'),
            TextField::new('slug', 'Slug')->setHelp('URL de la page'),
            ChoiceField::new('section', 'Section')
                ->setChoices([
                    'Philosophe' => 'philosophe',
                    'Musicienne' => 'musicienne',
                    'Marionnettiste' => 'marionnettiste',
                    'Thérapeute' => 'therapeute',
                    'Home' => 'home',
                    'Contact' => 'contact'
                ]),
            ChoiceField::new('parentId', 'Page parente')
                ->setChoices($parentChoices)
                ->setRequired(false)
                ->setHelp('À renseigner pour les pages de niveau 3 (sous-sous-menu)'),
            ChoiceField::new('subSection', 'Sous-section')  // 👈 Nouveau champ
                ->setChoices([
                    'Pianiste' => 'pianiste',
                    'Organiste' => 'organiste',
                    'Chef de chœur' => 'chef_de_choeur',
                ])
                ->setRequired(false),
            ChoiceField::new('type', 'Type')
                ->setChoices([
                    'Statique' => 'statique',
                    'Liste conférences' => 'conference_liste',
                    'Détail conférence' => 'conference_detail',
                    'Liste ateliers' => 'ateliers_liste',
                    'Répertoire solo' => 'repertoire_solo',
                    'Répertoire duo' => 'repertoire_duo',
                    'Spectacles' => 'spectacles',
                    'Section' => 'section'
                ])
                ->setRequired(false),
            NumberField::new('position', 'Position'),
            BooleanField::new('isActive', 'Active'),
            DateTimeField::new('createdAt', 'Créé le')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Modifié le')->hideOnForm(),
        ];
    }
}
