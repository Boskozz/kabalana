<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            EmailField::new('email', 'Email'),
            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Utilisateur (accès de base)' => 'ROLE_USER',
                    'Invité (morceaux partagés)' => 'ROLE_PARTAGE',
                    'Administrateur (accès complet)' => 'ROLE_ADMIN',
                ])
                ->allowMultipleChoices()
                ->renderAsBadges()
                ->setHelp('L\'accès à l\'administration nécessite le rôle Administrateur. Le rôle Invité débloque les morceaux/médias partagés.'),
            TextField::new('password', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->setFormTypeOption('attr', ['autocomplete' => 'new-password'])
                ->setRequired(Crud::PAGE_NEW === $pageName)
                ->onlyOnForms()
                ->setFormTypeOption('empty_data', '')
                ->setFormTypeOption('data', '')
                ->setHelp('Laissez vide pour conserver le mot de passe actuel.'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $entityInstance */
        if ($plainPassword = $entityInstance->getPassword()) {
            $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $plainPassword));
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $entityInstance */
        if ($plainPassword = $entityInstance->getPassword()) {
            $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $plainPassword));
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
}
