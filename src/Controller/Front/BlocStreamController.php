<?php

namespace App\Controller\Front;

use App\Entity\Bloc;
use App\Security\BlocAccessManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class BlocStreamController extends AbstractController
{
    #[Route('/bloc/{id}/audio', name: 'app_bloc_audio', requirements: ['id' => '\d+'])]
    public function audio(Bloc $bloc, BlocAccessManager $accessManager, TokenStorageInterface $tokenStorage): BinaryFileResponse
    {
        $user = $tokenStorage->getToken()?->getUser();
        if (!$accessManager->isAccessible($bloc, $user instanceof UserInterface ? $user : null)) {
            throw $this->createNotFoundException('Fichier introuvable');
        }

        $filename = $bloc->getPrivateFilename();
        if (!$filename) {
            throw $this->createNotFoundException('Fichier introuvable');
        }

        $path = $this->getParameter('kernel.project_dir') . '/var/uploads/bloc_audio/' . $filename;

        return new BinaryFileResponse(new File($path));
    }
}
