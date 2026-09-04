<?php

namespace App\Controller;

use App\Entity\Entry;
use App\Repository\EntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EntryController extends AbstractController
{
    #[Route('/carnet/{slug}', name: 'app_entry_show', methods: ['GET'])]
    public function show(string $slug, EntryRepository $entries): Response
    {
        $entry = $entries->findOneBySlug($slug);

        if (!$entry instanceof Entry) {
            throw $this->createNotFoundException('Cette entrée du carnet n\'existe pas.');
        }

        // Entrées précédente / suivante par référence (PRV-000X).
        $all = $entries->findForJournal(100);
        // Ordonnées par référence croissante pour une navigation naturelle.
        usort($all, static fn (Entry $a, Entry $b) => strcmp((string) $a->getReference(), (string) $b->getReference()));

        $prev = null;
        $next = null;
        foreach ($all as $i => $item) {
            if ($item->getId() === $entry->getId()) {
                $prev = $all[$i - 1] ?? null;
                $next = $all[$i + 1] ?? null;
                break;
            }
        }

        return $this->render('entry/show.html.twig', [
            'entry' => $entry,
            'prev' => $prev,
            'next' => $next,
        ]);
    }
}
