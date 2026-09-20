<?php

namespace App\Controller\Admin;

use App\Entity\Constat;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ConstatCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Constat::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Constat')
            ->setEntityLabelInPlural('Constats')
            ->setDefaultSort(['datePublication' => 'DESC', 'id' => 'DESC'])
            ->setSearchFields(['reference', 'enonce']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('reference', 'Référence')
            ->setHelp('Format C-01, C-02… (unique)')
            ->setColumns(4);
        yield AssociationField::new('entry', 'Entrée démontrée')
            ->setHelp('L\'entrée qui démontre ce constat.');
        yield TextareaField::new('enonce', 'Énoncé')
            ->setNumOfRows(3)
            ->setHelp('L\'affirmation, en une ou deux phrases.');
        yield DateField::new('datePublication', 'Date de publication');
        yield BooleanField::new('majeur', 'Constat majeur')
            ->setHelp('Le plus récent des majeurs est mis en avant sur l\'accueil.');
        yield BooleanField::new('retire', 'Retiré')
            ->setHelp('Un constat retiré reste affiché barré, jamais supprimé.');
    }
}
