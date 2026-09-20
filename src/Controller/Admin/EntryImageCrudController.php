<?php

namespace App\Controller\Admin;

use App\Entity\EntryImage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EntryImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EntryImage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Image du carnet')
            ->setEntityLabelInPlural('Images du carnet')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('entry', 'Article');
        yield ImageField::new('filename', 'Image')
            ->setBasePath('uploads/carnet')
            ->setUploadDir('public/uploads/carnet')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->setRequired(Crud::PAGE_NEW === $pageName);
        yield TextField::new('legende', 'Légende')
            ->setRequired(false)
            ->setHelp('Sert de légende sous l\'image et de texte alternatif.');
        yield IntegerField::new('position', 'Ordre')->hideOnIndex();
        yield TextField::new('markdown', 'Markdown à coller')
            ->hideOnForm()
            ->setHelp('Copie cette ligne dans le contenu de l\'article, à l\'endroit voulu.');
    }
}
