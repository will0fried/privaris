<?php

namespace App\Controller\Admin;

use App\Entity\Skill;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
// Les niveaux (current/target) ne sont plus notés : le radar est une carte des domaines.

class SkillCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Skill::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Compétence')
            ->setEntityLabelInPlural('Matrice de compétences')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('position', 'Ordre')
            ->setHelp('Le radar suit cet ordre.')
            ->setColumns(3);
        yield TextField::new('name', 'Domaine')->setColumns(5);
        yield TextField::new('shortLabel', 'Libellé court (radar)')
            ->setHelp('Nom raccourci affiché sur le radar.')
            ->setColumns(4);
    }
}
