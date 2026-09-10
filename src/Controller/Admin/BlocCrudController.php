<?php

namespace App\Controller\Admin;

use App\Entity\Bloc;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Form\Type\VichFileType;

class BlocCrudController extends AbstractCrudController
{
    /**
     * Modèles JSON insérés automatiquement dans le champ « Contenu »
     * lorsqu'un type de bloc est sélectionné.
     *
     * @var array<string, array<string, mixed>>
     */
    private const CONTENT_TEMPLATES = [
        'titre' => [
            'niveau' => 'h2',
            'texte' => 'Votre titre',
            'class' => 'text-center',
        ],
        'paragraphe' => [
            'texte' => 'Votre contenu',
            'class' => 'text-justify',
        ],
        'images_groupe' => [
            'images' => [
                ['media' => 12, 'class' => 'largeur-50'],
            ],
            'disposition' => 'horizontal',
        ],
        'liens' => [
            'url' => 'https://www.exemple.com',
            'texte' => 'Visiter le site',
            'target' => '_blank',
            'class' => 'btn btn-primary',
        ],
        'video' => [
            'url' => 'https://www.youtube.com/embed/IDENTIFIANT',
            'class' => 'ratio ratio-16x9',
        ],
        'audio' => [
            'file' => '/uploads/audio/interview.mp3',
            'title' => 'Mon interview radio',
            'legende' => 'Émission du 15 juin 2026',
            'transcription' => 'Bonjour, je suis Marion...',
            'autoplay' => false,
            'loop' => false,
        ],
        'document' => [
            'file' => '/uploads/documents/brochure.pdf',
            'title' => 'Brochure de présentation',
            'legende' => 'Téléchargez notre brochure',
            'icone' => 'file-pdf',
        ],
    ];

    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Bloc::class;
    }

    /**
     * Pré-remplit le nouveau bloc :
     *  - la page, à partir du filtre « page » de la liste (le bouton « Ajouter »
     *    d'EasyAdmin conserve les filtres de la page courante) ;
     *  - l'article et le numéro de bloc suivants, transmis par
     *    « Créer et ajouter un autre » pour enchaîner sans ressaisie.
     */
    public function createEntity(string $entityFqcn): Bloc
    {
        $bloc = new Bloc();

        $request = $this->getContext()?->getRequest();

        $filters = $request?->query->all('filters') ?? [];
        $pageFilter = $filters['page'] ?? null;
        $pageId = is_array($pageFilter) ? ($pageFilter['value'] ?? null) : null;

        if (null !== $pageId && '' !== $pageId) {
            $page = $this->pageRepository->find((int) $pageId);
            if (null !== $page) {
                $bloc->setPage($page);
            }
        }

        $nextArticle = $request?->query->get('nextArticle');
        if (null !== $nextArticle && '' !== $nextArticle) {
            $bloc->setArticleNumber((int) $nextArticle);
        }

        $nextBloc = $request?->query->get('nextBloc');
        if (null !== $nextBloc && '' !== $nextBloc) {
            $bloc->setBlocNumber((int) $nextBloc);
        }

        return $bloc;
    }

    /**
     * Après « Créer et ajouter un autre », on reporte l'article et le numéro de
     * bloc du bloc qui vient d'être enregistré (numéro + 1) dans l'URL du
     * formulaire suivant.
     */
    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        $submitButtonName = $context->getRequest()->request->all()['ea']['newForm']['btn'] ?? null;

        if (Action::SAVE_AND_ADD_ANOTHER === $submitButtonName) {
            $bloc = $context->getEntity()->getInstance();

            if ($bloc instanceof Bloc && null !== $bloc->getBlocNumber()) {
                $url = $this->container->get(AdminUrlGeneratorInterface::class)
                    ->setAction(Action::NEW)
                    ->set('nextArticle', $bloc->getArticleNumber())
                    ->set('nextBloc', $bloc->getBlocNumber() + 1)
                    ->generateUrl();

                return $this->redirect($url);
            }
        }

        return parent::getRedirectResponseAfterSave($context, $action);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort([
                'articleNumber' => 'ASC',
                'blocNumber' => 'ASC',
            ])
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('Bloc')
            ->setEntityLabelInPlural('Blocs');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('page', 'Page'))
            ->add(BooleanFilter::new('isActive', 'Actif'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $moveUp = Action::new('moveUp', 'Monter')
            ->linkToCrudAction('moveUp')
            ->setIcon('fa fa-arrow-up');

        $moveDown = Action::new('moveDown', 'Descendre')
            ->linkToCrudAction('moveDown')
            ->setIcon('fa fa-arrow-down');

        return $actions
            ->add(Crud::PAGE_INDEX, $moveDown)
            ->add(Crud::PAGE_INDEX, $moveUp);
    }

    #[AdminRoute('/{entityId:bloc.id}/move-up')]
    public function moveUp(Bloc $bloc): Response
    {
        $this->moveBloc($bloc, -1);

        return $this->redirectToReferer();
    }

    #[AdminRoute('/{entityId:bloc.id}/move-down')]
    public function moveDown(Bloc $bloc): Response
    {
        $this->moveBloc($bloc, 1);

        return $this->redirectToReferer();
    }

    /**
     * Échange la position du bloc avec son voisin immédiat (dans la page)
     * en permutant leurs clés de tri (articleNumber, blocNumber).
     */
    private function moveBloc(Bloc $bloc, int $direction): void
    {
        $page = $bloc->getPage();
        if (null === $page) {
            return;
        }

        /** @var Bloc[] $blocs */
        $blocs = $page->getBlocs()->toArray();

        $currentIndex = null;
        foreach ($blocs as $index => $candidate) {
            if ($candidate->getId() === $bloc->getId()) {
                $currentIndex = $index;
                break;
            }
        }

        if (null === $currentIndex) {
            return;
        }

        $neighbourIndex = $currentIndex + $direction;
        if (!isset($blocs[$neighbourIndex])) {
            return;
        }

        $neighbour = $blocs[$neighbourIndex];

        $article = $bloc->getArticleNumber();
        $number = $bloc->getBlocNumber();

        $bloc->setArticleNumber($neighbour->getArticleNumber());
        $bloc->setBlocNumber($neighbour->getBlocNumber());

        $neighbour->setArticleNumber($article);
        $neighbour->setBlocNumber($number);

        $this->entityManager->flush();
    }

    private function redirectToReferer(): Response
    {
        $request = $this->getContext()?->getRequest();
        $referer = $request?->headers->get('referer');

        return $this->redirect($referer ?? $this->generateUrl('admin_bloc_index'));
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
                ])
                ->renderAsNativeWidget()
                ->setFormTypeOption('attr', [
                    'data-bloc-type' => 'true',
                    'data-bloc-templates' => json_encode(self::CONTENT_TEMPLATES, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]),
            CodeEditorField::new('content', 'Contenu (JSON)')
                ->setLanguage('yaml')
                ->setFormTypeOption('attr', ['data-bloc-content' => 'true'])
                ->setHelp('
        <a href="#" data-bs-toggle="modal" data-bs-target="#bloc-content-help-modal">
            <i class="fa fa-book"></i> Voir la référence des modèles JSON
        </a>

        <div class="modal fade" id="bloc-content-help-modal" tabindex="-1" aria-labelledby="bloc-content-help-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bloc-content-help-modal-title">Référence JSON des blocs</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <p>
                            <strong>💡 Changer le « Type de bloc » insère automatiquement le modèle JSON correspondant.</strong><br>
                            <em>Si le champ contient déjà du JSON, une confirmation vous sera demandée avant de le remplacer.</em>
                        </p>
                        <hr>

                        <strong>Format JSON selon le type de bloc :</strong><br>

                        <strong>Titre :</strong><br>
                        <code>{"niveau":"h2","texte":"Votre titre","class":"text-center"}</code><br>

                        <strong>Paragraphe :</strong><br>
                        <code>{"texte":"Votre contenu","class":"text-justify"}</code><br>

                        <strong>Images :</strong><br>
                        <code>{"images": [ {"media": 12, "class": "largeur-50"}, {"media": 15, "class": "largeur-25"} ], "disposition": "horizontal"}</code><br>
                        <em>« media » = identifiant de l\'image dans la <strong>médiathèque</strong> (le texte alternatif et la légende y sont définis).</em><br>
                        <em>« class » (optionnel) = taille de l\'image : <code>largeur-25</code>, <code>largeur-50</code>, <code>largeur-75</code>, <code>largeur-100</code>.</em><br>

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
                        <em>Taille d\'une image (dans le JSON, champ <code>class</code> de chaque image) :</em> <code>largeur-25</code>, <code>largeur-50</code>, <code>largeur-75</code>, <code>largeur-100</code><br>
                        <em>Lien :</em> <code>lien-gauche</code>, <code>lien-centre</code>, <code>lien-droite</code><br>
                        <em>Bouton coloré :</em> <code>btn-bleu</code>, <code>btn-vert</code>, <code>btn-rouge</code>, <code>btn-jaune</code>, <code>btn-gris</code>, <code>btn-cyan</code><br>
                        <em>Texte :</em> <code>texte-gauche</code>, <code>texte-centre</code>, <code>texte-droite</code>, <code>texte-justifie</code>, <code>texte-grand</code><br>
                        <em>Espace :</em> <code>marge-haut</code>, <code>marge-bas</code>, <code>marge-auto</code>, <code>espace-tout</code>, <code>largeur-50</code>…
                    </div>
                </div>
            </div>
        </div>
    ')
                ->formatValue(function ($value) {
                    // Si le contenu est un tableau (ancienne version), le convertir en JSON
                    if (is_array($value)) {
                        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }
                    return $value ?? '{}';
                }),
            NumberField::new('position', 'Position globale')->hideOnForm(),
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
