<?php

namespace App\Controller;

use App\Repository\ConstatRepository;
use App\Repository\EntryRepository;
use App\Repository\SkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(SkillRepository $skills, EntryRepository $entries, ConstatRepository $constats): Response
    {
        $majeur = $constats->findConstatMajeur();

        // Vedette : la dernière entrée qui a produit au moins un constat,
        // sinon repli sur la dernière entrée publiée (pour ne jamais laisser la section vide).
        $featured = $entries->findLatestWithConstats() ?? ($entries->findPublished(1)[0] ?? null);

        // Les autres entrées récentes (hors la vedette), 3 max, en lignes compactes.
        $recent = $entries->findForJournal(5);
        $others = array_values(array_filter(
            $recent,
            static fn ($e): bool => null === $featured || $e->getId() !== $featured->getId()
        ));

        $lastPublished = $entries->findPublished(1)[0] ?? null;

        return $this->render('home/index.html.twig', [
            'skills' => $skills->findAllOrdered(),
            'constatMajeur' => $majeur,
            'constats' => $constats->findRecents(4, $majeur),
            'constatsTotal' => $constats->countAll(),
            'featuredEntry' => $featured,
            'entries' => \array_slice($others, 0, 3),
            'entriesTotal' => $entries->countForJournal(),
            'heroConstats' => $constats->countActifs(),
            'heroEntrees' => $entries->countPublished(),
            'heroLastDate' => $lastPublished?->getPublishedAt(),
        ]);
    }
}
