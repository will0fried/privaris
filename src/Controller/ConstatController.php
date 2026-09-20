<?php

namespace App\Controller;

use App\Repository\ConstatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConstatController extends AbstractController
{
    #[Route('/constats', name: 'app_constat_index', methods: ['GET'])]
    public function index(ConstatRepository $constats): Response
    {
        $all = $constats->findAllRecent();

        return $this->render('constat/index.html.twig', [
            'actifs' => array_values(array_filter($all, static fn ($c): bool => !$c->isRetire())),
            'retires' => array_values(array_filter($all, static fn ($c): bool => $c->isRetire())),
        ]);
    }
}
