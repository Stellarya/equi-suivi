<?php

namespace App\Controller\Admin;

use App\Controller\Admin\BreedCrudController;
use App\Controller\Admin\CoatCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator
    )
    {
    }

    public function index(): RedirectResponse
    {
        $url = $this->adminUrlGenerator->setController(BreedCrudController::class)->generateUrl();
        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Equi Suivi');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkTo(DisciplineCrudController::class, 'Discipline', 'fas fa-list');
        yield MenuItem::linkTo(CategoryCrudController::class, 'Catégorie', 'fas fa-list');
        yield MenuItem::linkTo(ProtocolFigureCrudController::class, 'Figure Protocole', 'fas fa-list');
        yield MenuItem::linkTo(ProtocolMovementCrudController::class, 'Mouvement Protocole', 'fas fa-list');
        yield MenuItem::linkTo(GalopCrudController::class, 'Galop', 'fas fa-list');
        yield MenuItem::linkTo(LevelCrudController::class, 'Niveau', 'fas fa-list');
        yield MenuItem::linkTo(BreedCrudController::class, 'Races', 'fas fa-list');
        yield MenuItem::linkTo(DressageTestCrudController::class, 'Reprise Dressage', 'fas fa-list');
        yield MenuItem::linkTo(CoatCrudController::class, 'Robes', 'fas fa-list');
        yield MenuItem::linkTo(StatusCompetitionCrudController::class, 'Statut Compétition', 'fas fa-list');
        yield MenuItem::linkTo(TypeEquitationCrudController::class, 'Type Equitation', 'fas fa-list');
        yield MenuItem::linkTo(TypeCareCrudController::class, 'Type Soins', 'fas fa-list');
        yield MenuItem::linkTo(TypeSaddleCrudController::class, 'Type Selle', 'fas fa-list');
        yield MenuItem::linkTo(TypeTestCrudController::class, 'TypeReprise', 'fas fa-list');
    }
}
