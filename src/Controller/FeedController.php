<?php

namespace App\Controller;

use App\Repository\EntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FeedController extends AbstractController
{
    /**
     * Flux RSS du carnet — permet aux agrégateurs et aux outils d'automatisation
     * (Buffer, Zapier, Mailbrew…) de récupérer les nouvelles entrées publiées.
     */
    #[Route('/rss', name: 'app_rss', methods: ['GET'])]
    public function rss(EntryRepository $entries): Response
    {
        $xml = $this->renderView('feed/rss.xml.twig', [
            'entries' => $entries->findPublished(30),
        ]);

        return new Response($xml, Response::HTTP_OK, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }

    /**
     * Plan du site pour les moteurs de recherche.
     */
    #[Route('/sitemap.xml', name: 'app_sitemap', methods: ['GET'])]
    public function sitemap(EntryRepository $entries): Response
    {
        $xml = $this->renderView('feed/sitemap.xml.twig', [
            'entries' => $entries->findPublished(200),
        ]);

        return new Response($xml, Response::HTTP_OK, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * robots.txt généré dynamiquement (l'URL du sitemap s'adapte au domaine).
     */
    #[Route('/robots.txt', name: 'app_robots', methods: ['GET'])]
    public function robots(): Response
    {
        $sitemap = $this->generateUrl('app_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $content = "User-agent: *\n"
            ."Allow: /\n"
            ."Disallow: /admin\n"
            ."Disallow: /connexion\n"
            ."Sitemap: ".$sitemap."\n";

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
