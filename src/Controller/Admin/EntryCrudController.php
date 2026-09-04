<?php

namespace App\Controller\Admin;

use App\Entity\Entry;
use App\Enum\EntryStatus;
use App\Enum\EntryType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class EntryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Entry::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Entrée')
            ->setEntityLabelInPlural('Entrées du carnet')
            ->setDefaultSort(['reference' => 'DESC'])
            ->setSearchFields(['reference', 'title', 'excerpt'])
            ->setPaginatorPageSize(30);
    }

    public function configureFields(string $pageName): iterable
    {
        $typeChoices = array_combine(
            array_map(static fn (EntryType $t) => $t->label(), EntryType::cases()),
            EntryType::cases(),
        );
        $statusChoices = array_combine(
            array_map(static fn (EntryStatus $s) => $s->label(), EntryStatus::cases()),
            EntryStatus::cases(),
        );

        yield FormField::addColumn('col-lg-8');

        yield FormField::addFieldset('Identité')->collapsible();
        yield TextField::new('reference', 'Référence')
            ->setHelp('Ex. PRV-0001')
            ->setColumns(4);
        yield TextField::new('title', 'Titre')->setColumns(8);
        yield SlugField::new('slug', 'Slug (URL)')
            ->setTargetFieldName('title')
            ->setHelp('Laisser vide pour le générer automatiquement.')
            ->hideOnIndex();
        yield ChoiceField::new('type', 'Type')
            ->setChoices($typeChoices)
            ->setFormTypeOption('choice_value', static fn (?EntryType $t) => $t?->value)
            ->renderAsBadges()
            ->setColumns(6);
        yield ChoiceField::new('status', 'Statut')
            ->setChoices($statusChoices)
            ->setFormTypeOption('choice_value', static fn (?EntryStatus $s) => $s?->value)
            ->renderAsBadges()
            ->setColumns(6);

        yield FormField::addFieldset('Résumé')->collapsible();
        yield TextareaField::new('excerpt', 'Chapô / résumé')
            ->setNumOfRows(3)
            ->hideOnIndex();

        yield FormField::addFieldset('Contenu du carnet')
            ->setHelp('Objectif → Protocole → Observations → Ce que j\'en retiens. Markdown léger accepté.')
            ->collapsible();
        yield TextareaField::new('objectif', 'Objectif')->setNumOfRows(4)->hideOnIndex();
        yield TextareaField::new('protocole', 'Protocole')->setNumOfRows(8)->hideOnIndex();
        yield TextareaField::new('observations', 'Observations')->setNumOfRows(6)->hideOnIndex();
        yield TextareaField::new('retiens', 'Ce que j\'en retiens')->setNumOfRows(5)->hideOnIndex();

        yield FormField::addColumn('col-lg-4');

        yield FormField::addFieldset('Publication');
        yield DateTimeField::new('publishedAt', 'Publié le')
            ->setHelp('Rempli seulement quand l\'entrée est publiée.')
            ->hideOnIndex();
        yield TextField::new('planningLabel', 'Étiquette de statut')
            ->setHelp('Ex. « En rédaction », « Planifié · M2 » — affichée dans le journal si non publié.')
            ->hideOnIndex();

        yield FormField::addFieldset('Métadonnées');
        yield TextField::new('terrain', 'Terrain')->hideOnIndex();
        yield TextField::new('duree', 'Durée réelle')->hideOnIndex();
        yield TextField::new('outils', 'Outils')->hideOnIndex();
        yield TextField::new('prerequis', 'Prérequis')->hideOnIndex();
        yield IntegerField::new('readingMinutes', 'Minutes de lecture')->hideOnIndex();
        yield UrlField::new('videoUrl', 'Vidéo (URL)')->hideOnIndex();
    }
}
