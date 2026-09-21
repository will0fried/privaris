<?php

namespace App\Controller\Admin;

use App\Entity\Entry;
use App\Enum\EntryStatus;
use App\Enum\EntrySerie;
use App\Enum\EntryType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
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

    public function configureAssets(Assets $assets): Assets
    {
        // N'affiche que le bloc de contenu correspondant au Type choisi.
        $js = <<<'HTML'
<script>
(function () {
    function apply() {
        var sel = document.querySelector('select[name$="[type]"]');
        if (!sel) { return; }
        var structured = (sel.value === 'lab' || sel.value === 'writeup');
        document.querySelectorAll('.js-body-structured').forEach(function (el) { el.style.display = structured ? '' : 'none'; });
        document.querySelectorAll('.js-body-free').forEach(function (el) { el.style.display = structured ? 'none' : ''; });
    }
    document.addEventListener('DOMContentLoaded', function () {
        var sel = document.querySelector('select[name$="[type]"]');
        if (!sel) { return; }
        sel.addEventListener('change', apply);
        apply();
    });
})();
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<style>
.editor-toolbar .mde-t{font-family:-apple-system,"Segoe UI",Roboto,Arial,sans-serif!important;width:auto!important;min-width:32px;padding:0 8px!important;font-size:12px;line-height:30px;color:#2b2f36}
.editor-toolbar .mde-t::before{font-family:inherit!important;font-size:inherit}
.editor-toolbar .mde-bold::before{content:"B";font-weight:800}
.editor-toolbar .mde-italic::before{content:"I";font-style:italic;font-weight:600}
.editor-toolbar .mde-head::before{content:"H";font-weight:700}
.editor-toolbar .mde-quote::before{content:"\201D";font-weight:800}
.editor-toolbar .mde-ul::before{content:"\2022 \2022";letter-spacing:1px;font-weight:800}
.editor-toolbar .mde-ol::before{content:"1."}
.editor-toolbar .mde-link::before{content:"Lien";font-size:11px}
.editor-toolbar .mde-img::before{content:"Image";font-size:11px;font-weight:700;color:#b9770e}
.editor-toolbar .mde-code::before{content:"</>";font-size:11px;font-weight:700}
.editor-toolbar .mde-eye::before{content:"Aperçu";font-size:11px}
</style>
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
<script>
(function () {
    var SUFFIXES = ['[content]', '[objectif]', '[protocole]', '[observations]', '[retiens]'];
    window.addEventListener('load', function () {
        if (typeof EasyMDE === 'undefined') { return; }
        var editors = [];
        SUFFIXES.forEach(function (suffix) {
            var ta = document.querySelector('textarea[name$="' + suffix + '"]');
            if (!ta || ta.dataset.mdeReady) { return; }
            ta.dataset.mdeReady = '1';
            var mde = new EasyMDE({
                element: ta,
                spellChecker: false,
                status: false,
                autoDownloadFontAwesome: false,
                minHeight: '160px',
                uploadImage: true,
                imageMaxSize: 8 * 1024 * 1024,
                imageAccept: 'image/png, image/jpeg, image/webp, image/gif',
                imageUploadEndpoint: '/admin/carnet/upload-image',
                imageTexts: {
                    sbInit: 'Glissez une image ici, collez-la (Cmd+V), ou utilisez le bouton image.',
                    sbOnDragEnter: 'Déposez pour envoyer l\'image',
                    sbOnDrop: 'Envoi de l\'image en cours…',
                    sbProgress: 'Envoi ({{progress}}%)',
                    sbOnUploaded: 'Image ajoutée'
                },
                errorMessages: { imageTooLarge: 'Image trop lourde (8 Mo maximum).' },
                toolbar: [{name:'bold',action:EasyMDE.toggleBold,className:'mde-t mde-bold',title:'Gras'},{name:'italic',action:EasyMDE.toggleItalic,className:'mde-t mde-italic',title:'Italique'},{name:'heading',action:EasyMDE.toggleHeadingSmaller,className:'mde-t mde-head',title:'Titre'},'|',{name:'quote',action:EasyMDE.toggleBlockquote,className:'mde-t mde-quote',title:'Citation'},{name:'ul',action:EasyMDE.toggleUnorderedList,className:'mde-t mde-ul',title:'Liste à puces'},{name:'ol',action:EasyMDE.toggleOrderedList,className:'mde-t mde-ol',title:'Liste numérotée'},'|',{name:'link',action:EasyMDE.drawLink,className:'mde-t mde-link',title:'Lien'},{name:'upload-image',action:EasyMDE.drawUploadedImage,className:'mde-t mde-img',title:'Ajouter une image (ou glisser-déposer / coller)'},{name:'code',action:EasyMDE.toggleCodeBlock,className:'mde-t mde-code',title:'Bloc de code'},'|',{name:'preview',action:EasyMDE.togglePreview,className:'mde-t mde-eye no-disable',title:'Aperçu'}]
            });
            editors.push(mde);
        });
        function refresh() { editors.forEach(function (e) { try { e.codemirror.refresh(); } catch (err) {} }); }
        setTimeout(refresh, 80);
        var sel = document.querySelector('select[name$="[type]"]');
        if (sel) { sel.addEventListener('change', function () { setTimeout(refresh, 80); }); }
    });
})();
</script>
HTML;

        return $assets->addHtmlContentToBody($js);
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
        yield ChoiceField::new('serie', 'Série')
            ->setChoices(array_combine(
                array_map(static fn (EntrySerie $s) => $s->label(), EntrySerie::cases()),
                EntrySerie::cases(),
            ))
            ->setFormTypeOption('choice_value', static fn (?EntrySerie $s) => $s?->value)
            ->setRequired(false)
            ->renderAsBadges()
            ->setColumns(6)
            ->setHelp('Optionnel — Rejeu (rejouer un incident) ou VenduIA (tester un outil vendu IA). Vide = hors serie.');

        yield FormField::addFieldset('Résumé')->collapsible();
        yield TextareaField::new('excerpt', 'Chapô / résumé')
            ->setNumOfRows(3)
            ->hideOnIndex();

        yield FormField::addFieldset('Contenu structuré · Labs & Writeups')
            ->setHelp('Objectif → Protocole → Observations → Ce que j\'en retiens. Markdown léger accepté.')
            ->setCssClass('js-body-structured')
            ->collapsible();
        yield TextareaField::new('objectif', 'Objectif')->setNumOfRows(4)->hideOnIndex();
        yield TextareaField::new('protocole', 'Protocole')->setNumOfRows(8)->hideOnIndex();
        yield TextareaField::new('observations', 'Observations')->setNumOfRows(6)->hideOnIndex();
        yield TextareaField::new('retiens', 'Ce que j\'en retiens')->setNumOfRows(5)->hideOnIndex();

        yield FormField::addFieldset('Contenu libre · Décryptage & Coulisses')
            ->setHelp('Un seul champ, en Markdown (gras, listes, `code`, blocs ```). Remplace les 4 champs ci-dessus pour les articles.')
            ->setCssClass('js-body-free')
            ->collapsible();
        yield TextareaField::new('content', 'Contenu (Markdown)')->setNumOfRows(18)->hideOnIndex();

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
        yield TextField::new('echantillon', 'Échantillon')->hideOnIndex();
        yield TextField::new('duree', 'Durée réelle')->hideOnIndex();
        yield TextField::new('outils', 'Outils')->hideOnIndex();
        yield TextField::new('prerequis', 'Prérequis')->hideOnIndex();
        yield IntegerField::new('readingMinutes', 'Minutes de lecture')->hideOnIndex();
        yield UrlField::new('videoUrl', 'Vidéo (URL)')->hideOnIndex();

        yield FormField::addFieldset('Partage (réseaux sociaux)');
        yield ImageField::new('shareImage', 'Image de partage')
            ->setBasePath('uploads/carnet')
            ->setUploadDir('public/uploads/carnet')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->setRequired(false)
            ->setHelp('Aperçu LinkedIn / Twitter (idéal ~1200×630). Sans image, l\'aperçu par défaut du site est utilisé.')
            ->hideOnIndex();
    }
}
