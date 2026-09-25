<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/api', name: 'api_home', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'application' => 'Suivi et gestion de pepiniere forestiere',
            'framework'   => 'Symfony 8.1',
            'endpoints'   => [
                'GET    /api/pepinieres',
                'GET    /api/pepinieres/{id}',
                'POST   /api/pepinieres',
                'PUT    /api/pepinieres/{id}',
                'DELETE /api/pepinieres/{id}',
                'GET    /api/lots',
                'GET    /api/lots/{id}',
                'POST   /api/lots',
                'GET    /api/indicateurs/lots',
                'GET    /api/indicateurs/tableau-bord',
                'GET    /api/indicateurs/rendement',
            ],
        ]);
    }
}
