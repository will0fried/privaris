<?php

namespace App\Controller\Admin;

use App\Entity\Subscriber;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;

class SubscriberCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Subscriber::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Abonné')
            ->setEntityLabelInPlural('Abonnés newsletter')
            ->setDefaultSort(['subscribedAt' => 'DESC'])
            ->setSearchFields(['email']);
    }

    public function configureActions(Actions $actions): Actions
    {
        // Les abonnés s'inscrivent depuis le site ; on ne les crée pas à la main.
        return $actions->disable(Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        yield EmailField::new('email', 'E-mail');
        yield BooleanField::new('confirmed', 'Confirmé')->renderAsSwitch(false);
        yield DateTimeField::new('subscribedAt', 'Inscrit le')->hideOnForm();
        yield DateTimeField::new('confirmedAt', 'Confirmé le')->hideOnForm();
    }
}
