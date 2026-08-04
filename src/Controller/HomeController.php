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
        return $this->render('home/index.html.twig');
    }

    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'dashboard' => [
                'currentWeight' => 74.5,
                'weightVariation' => -0.5,
                'targetWeight' => 70,
                'goalProgress' => 68,
                'bmi' => 23.5,
                'bmiStatus' => 'Bonne santé',
                'weightHistory' => [
                    'labels' => [
                        '1 oct.', '2 oct.', '3 oct.', '4 oct.', '5 oct.',
                        '6 oct.', '7 oct.', '8 oct.', '9 oct.', '10 oct.',
                        '11 oct.', '12 oct.', '13 oct.', '14 oct.', '15 oct.',
                        '16 oct.', '17 oct.', '18 oct.', '19 oct.', '20 oct.',
                        '21 oct.', '22 oct.', '23 oct.', '24 oct.', '25 oct.',
                        '26 oct.', '27 oct.', '28 oct.', '29 oct.', '30 oct.',
                    ],
                    'values' => [
                        74.9, 75.0, 74.8, 74.6, 74.7,
                        75.1, 75.5, 75.3, 74.8, 74.5,
                        74.9, 75.6, 76.0, 75.7, 75.0,
                        74.4, 74.2, 74.8, 75.7, 76.2,
                        76.0, 75.2, 74.5, 74.1, 74.7,
                        75.5, 75.9, 75.2, 74.7, 74.5,
                    ],
                ],
                'coachFeedback' => 'Excellent travail cette semaine, continue comme ça ! Votre régularité dans votre apport en protéines se reflète vraiment dans vos indicateurs de récupération.',
                'coachName' => 'Coach Mathieu',
            ],
        ]);
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
