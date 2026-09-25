<?php

namespace App\Controller;

use App\Entity\Distribution;
use App\Entity\Lot;
use App\Entity\Pepiniere;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(private  EntityManagerInterface $em)
    {
    }

    #[Route('/', name: 'dashboard', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->getUser()){return $this->redirectToRoute("app_login");}
        $lots = $this->em->getRepository(Lot::class)->findAll();
        $pepinieres = $this->em->getRepository(Pepiniere::class)->findAll();

        $totalGraines = 0;
        $totalLeves = 0;
        $totalPerdus = 0;
        $indicateurs = [];

        foreach ($lots as $lot) {
            $leves = $lot->getNbPlantsLeves() ?? 0;
            $perdus = 0;
            foreach ($lot->getPertes() as $perte) {
                $perdus += $perte->getNbPlantsPerdus() ?? 0;
            }
            $totalGraines += $lot->getNbGrainesSemees() ?? 0;
            $totalLeves += $leves;
            $totalPerdus += $perdus;

            $indicateurs[] = [
                'lot'             => $lot,
                'tauxSurvie'      => $leves > 0 ? round(($leves - $perdus) * 100 / $leves, 1) : null,
                'tauxMortalite'   => $leves > 0 ? round($perdus * 100 / $leves, 1) : null,
                'perdus'          => $perdus,
            ];
        }

        $distribues = (int) $this->em->createQuery(
            'SELECT COALESCE(SUM(d.quantite), 0) FROM ' . Distribution::class . ' d'
        )->getSingleScalarResult();

        return $this->render('dashboard/index.html.twig', [
            'nbPepinieres'    => count($pepinieres),
            'nbLots'          => count($lots),
            'totalGraines'    => $totalGraines,
            'totalLeves'      => $totalLeves,
            'totalPerdus'     => $totalPerdus,
            'totalDistribues' => $distribues,
            'tauxGerminationGlobal' => $totalGraines > 0 ? round($totalLeves * 100 / $totalGraines, 1) : 0,
            'indicateurs'     => $indicateurs,
        ]);
    }
}
