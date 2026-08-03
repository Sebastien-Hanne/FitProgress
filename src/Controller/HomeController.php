<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(): Response
    {
        return new Response('FitProgress');
    }

    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return new Response('Tableau de bord utilisateur en cours de développement.');
    }

    #[Route('/coach/dashboard', name: 'coach_dashboard', methods: ['GET'])]
    public function coachDashboard(): Response
    {
        return new Response('Tableau de bord coach en cours de développement.');
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function adminDashboard(): Response
    {
        return new Response('Tableau de bord administrateur en cours de développement.');
    }
}
