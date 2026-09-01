<?php

namespace App\Controller\Front;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(EntityManagerInterface $em): Response
    {
         $page = $em->getRepository(Page::class)->findOneBy([
            'section' => 'Home',
            'isActive' => true
        ]);

        if (!$page) {
            throw $this->createNotFoundException('Page non trouvée');
        }
        // Rediriger vers la page d'accueil
        return $this->render('front/page/show.html.twig', [
            'page' => $page,
        ]);
    }

    
    // #[Route('/contact', name: 'app_page_contact')]
    // public function contact(EntityManagerInterface $em): Response {
    //     $page = $em->getRepository(Page::class)->findOneBy([
    //         'slug' => 'contact',
    //         'isActive' => true
    //     ]);

    //     if (!$page) {
    //         throw $this->createNotFoundException('Page non trouvée');
    //     }
    //     return $this->render('front/page/contact.html.twig', [
    //         'page' => $page,
    //     ]);
    // }

    #[Route('/{slug}', name: 'app_page_show', requirements: ['slug' => '^(?!admin|login|logout|bloc|_wdt|_profiler|_error).+'])]
    public function show(string $slug, EntityManagerInterface $em): Response
    {
        $page = $em->getRepository(Page::class)->findOneBy([
            'slug' => $slug,
            'isActive' => true
        ]);

        if (!$page) {
            throw $this->createNotFoundException('Page non trouvée');
        }

        return $this->render('front/page/show.html.twig', [
            'page' => $page,
        ]);
    }

}
