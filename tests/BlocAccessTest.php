<?php

namespace App\Tests;

use App\Entity\Bloc;
use App\Entity\Page;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BlocAccessTest extends WebTestCase
{
    private function createUser(EntityManagerInterface $em, UserPasswordHasherInterface $hasher, string $email, array $roles): User
    {
        $user = (new User())->setEmail($email)->setRoles($roles);
        $user->setPassword($hasher->hashPassword($user, 'password'));
        $em->persist($user);
        return $user;
    }

    public function testRestrictedAndExpiredBlocs(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $guest = $this->createUser($em, $hasher, 'guest@example.com', ['ROLE_PARTAGE']);
        $other = $this->createUser($em, $hasher, 'other@example.com', ['ROLE_USER']);
        $admin = $this->createUser($em, $hasher, 'admin@example.com', ['ROLE_ADMIN']);

        $privateDir = dirname(__DIR__) . '/var/uploads/bloc_audio';
        if (!is_dir($privateDir)) {
            mkdir($privateDir, 0777, true);
        }
        $privateFile = $privateDir . '/test-piste.mp3';
        file_put_contents($privateFile, 'FAKE-MP3-CONTENT');

        $page = (new Page())
            ->setSlug('repertoire-solo')
            ->setTitle('Répertoire solo')
            ->setSection('musicienne')
            ->setPosition(1);
        $em->persist($page);

        $publicBloc = (new Bloc())
            ->setType('audio')
            ->setContent(json_encode(['file' => '/uploads/audio/public.mp3', 'title' => 'Public']))
            ->setPosition(1);
        $publicBloc->setIsActive(true);
        $em->persist($publicBloc);
        $page->addBloc($publicBloc);

        $privateBloc = (new Bloc())
            ->setType('audio')
            ->setContent(json_encode(['title' => 'Privé']))
            ->setRequiredRole('ROLE_PARTAGE')
            ->setPrivateFilename('test-piste.mp3')
            ->setPosition(2);
        $privateBloc->setIsActive(true);
        $em->persist($privateBloc);
        $page->addBloc($privateBloc);

        $expiredBloc = (new Bloc())
            ->setType('audio')
            ->setContent(json_encode(['title' => 'Expiré']))
            ->setRequiredRole('ROLE_PARTAGE')
            ->setPrivateFilename('test-piste.mp3')
            ->setExpiresAt(new \DateTime('-1 day'))
            ->setPosition(3);
        $expiredBloc->setIsActive(true);
        $em->persist($expiredBloc);
        $page->addBloc($expiredBloc);

        $em->flush();

        $privateUrl = '/bloc/' . $privateBloc->getId() . '/audio';
        $expiredUrl = '/bloc/' . $expiredBloc->getId() . '/audio';

        // 1. Anonymous: public visible, private hidden, expired hidden
        $client->request('GET', '/repertoire-solo');
        $html = $client->getResponse()->getContent();
        if (200 !== $client->getResponse()->getStatusCode()) {
            fwrite(STDERR, "\n--- BODY ---\n".substr($html, 0, 4000)."\n--- END ---\n");
        }
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/uploads/audio/public.mp3', $html, 'public audio visible');
        $this->assertStringNotContainsString($privateUrl, $html, 'private audio hidden for anonymous');
        $this->assertStringNotContainsString($expiredUrl, $html, 'expired audio hidden for anonymous');

        // 2. Stream as anonymous -> 404
        $client->request('GET', $privateUrl);
        $this->assertSame(404, $client->getResponse()->getStatusCode(), 'anonymous stream denied');

        // 3. Other user (no role) -> hidden + stream 404
        $client->loginUser($other);
        $client->request('GET', '/repertoire-solo');
        $html = $client->getResponse()->getContent();
        $this->assertStringNotContainsString($privateUrl, $html, 'private hidden for ROLE_USER');
        $client->request('GET', $privateUrl);
        $this->assertSame(404, $client->getResponse()->getStatusCode(), 'ROLE_USER stream denied');

        // 4. Guest (ROLE_PARTAGE) -> visible + stream 200, expired hidden/denied
        $client->loginUser($guest);
        $client->request('GET', '/repertoire-solo');
        $html = $client->getResponse()->getContent();
        $this->assertStringContainsString($privateUrl, $html, 'private visible for guest');
        $this->assertStringNotContainsString($expiredUrl, $html, 'expired hidden for guest too');
        $client->request('GET', $privateUrl);
        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'guest stream allowed');
        $client->request('GET', $expiredUrl);
        $this->assertSame(404, $client->getResponse()->getStatusCode(), 'expired stream denied for guest');

        // 5. Admin bypass
        $client->loginUser($admin);
        $client->request('GET', $privateUrl);
        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'admin stream allowed');
    }

    public function testAdminCanEditBlocRestrictedFields(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $admin = (new User())->setEmail('admin2@example.com')->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($hasher->hashPassword($admin, 'password'));
        $em->persist($admin);

        $page = (new Page())->setSlug('repertoire-duo')->setTitle('Répertoire duo')->setSection('musicienne')->setPosition(1);
        $em->persist($page);

        $bloc = (new Bloc())
            ->setType('audio')
            ->setContent(json_encode(['file' => '/uploads/audio/x.mp3']))
            ->setRequiredRole('ROLE_PARTAGE')
            ->setExpiresAt(new \DateTime('+30 days'))
            ->setPosition(1);
        $bloc->setIsActive(true);
        $em->persist($bloc);
        $page->addBloc($bloc);
        $em->flush();

        $client->loginUser($admin);
        $client->request('GET', '/admin/bloc/' . $bloc->getId() . '/edit');
        if (200 !== $client->getResponse()->getStatusCode()) {
            fwrite(STDERR, "\n--- EDIT BODY ---\n".substr($client->getResponse()->getContent() ?? '', 0, 4000)."\n--- END ---\n");
        }
        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'edit form renders');
        $this->assertStringContainsString('ROLE_PARTAGE', $client->getResponse()->getContent(), 'role choice present');

        $client->request('GET', '/admin/bloc/new');
        $this->assertSame(200, $client->getResponse()->getStatusCode(), 'new form renders');
    }
}
