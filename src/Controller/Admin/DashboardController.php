<?php

namespace App\Controller\Admin;

use App\Entity\Entry;
use App\Entity\Skill;
use App\Entity\Subscriber;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(EntryCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<span style="letter-spacing:.04em">PRIVARIS</span> <span style="color:#F6A733">·</span> Admin')
            ->setFaviconPath('favicon.svg')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-gauge-high');

        yield MenuItem::section('Carnet');
        yield MenuItem::linkToCrud('Entrées', 'fa fa-book-open', Entry::class);
        yield MenuItem::linkToCrud('Compétences', 'fa fa-chart-line', Skill::class);

        yield MenuItem::section('Audience');
        yield MenuItem::linkToCrud('Abonnés', 'fa fa-envelope', Subscriber::class);

        yield MenuItem::section('Système');
        yield MenuItem::linkToCrud('Administrateurs', 'fa fa-user-shield', User::class);
        yield MenuItem::linkToUrl('Voir le site', 'fa fa-arrow-up-right-from-square', '/')
            ->setLinkTarget('_blank');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-right-from-bracket');
    }
}
