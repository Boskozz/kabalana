<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use App\Repository\PageRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private PageRepository $pageRepository,
        private AdminUrlGenerator $adminUrlGenerator,
    ) {}

    public function index(): Response
    {
        return $this->redirectToRoute('admin_page_index');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration - A. Kabalan');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addAssetMapperEntry('admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Contenu');
        yield MenuItem::linkToRoute('📄 Pages', 'fas fa-file', 'admin_page_index');
        yield MenuItem::linkToRoute('🧩 Blocs', 'fas fa-cubes', 'admin_bloc_index');

        yield MenuItem::section('Blocs par page');
        yield from $this->buildBlocsByPageMenuItems();

        yield MenuItem::section('Médias');
        yield MenuItem::linkToRoute('📁 Médiathèque', 'fas fa-photo-video', 'admin_media_index');

        yield MenuItem::section('Navigation');
        yield MenuItem::linkToRoute('🌐 Voir le site', 'fas fa-globe', 'app_home');

        yield MenuItem::section('Administration');
        yield MenuItem::linkTo(UserCrudController::class, '👥 Utilisateurs', 'fas fa-users');
    }

    /**
     * Un sous-menu par section, chaque page ouvrant la liste des blocs filtrée
     * pour cette page (triée par article puis bloc).
     *
     * @return iterable<\EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface>
     */
    private function buildBlocsByPageMenuItems(): iterable
    {
        $pages = $this->pageRepository->findAllActiveOrdered();
        $pagesBySection = [];
        foreach ($pages as $page) {
            $pagesBySection[$page->getSection()][] = $page;
        }

        foreach ($pagesBySection as $section => $sectionPages) {
            $subItems = [];
            foreach ($sectionPages as $page) {
                $url = $this->adminUrlGenerator
                    ->setController(BlocCrudController::class)
                    ->setAction('index')
                    ->setAll(['filters' => ['page' => ['comparison' => '=', 'value' => $page->getId()]]])
                    ->generateUrl();

                $subItems[] = MenuItem::linkToUrl($page->getTitle(), null, $url);
            }

            yield MenuItem::subMenu(ucfirst($section), 'fas fa-layer-group')
                ->setSubItems($subItems);
        }
    }
}
