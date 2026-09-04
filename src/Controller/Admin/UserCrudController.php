<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Administrateur')
            ->setEntityLabelInPlural('Administrateurs')
            ->setSearchFields(['email', 'displayName']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield EmailField::new('email', 'E-mail');
        yield TextField::new('displayName', 'Nom affiché')->hideOnIndex();
        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices(['Administrateur' => 'ROLE_ADMIN'])
            ->allowMultipleChoices()
            ->renderAsBadges()
            ->setHelp('ROLE_USER est ajouté automatiquement.');
        yield TextField::new('plainPassword', 'Mot de passe')
            ->setFormType(\Symfony\Component\Form\Extension\Core\Type\PasswordType::class)
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->setHelp('Laisser vide en édition pour ne pas changer le mot de passe.')
            ->onlyOnForms();
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $this->hashPlainPassword($entityInstance);
        }
        parent::persistEntity($em, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $this->hashPlainPassword($entityInstance);
        }
        parent::updateEntity($em, $entityInstance);
    }

    private function hashPlainPassword(User $user): void
    {
        $plain = $user->getPlainPassword();
        if ($plain) {
            $user->setPassword($this->hasher->hashPassword($user, $plain));
            $user->eraseCredentials();
        }
    }
}
