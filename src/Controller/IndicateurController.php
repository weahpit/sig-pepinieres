<?php

namespace App\Controller;

use App\Entity\Lot;
use App\Entity\Pepiniere;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Indicateurs de performance (equivalents des vues SQL v_indicateurs_lot,
 * v_tableau_bord et v_rendement_pepiniere).
 */
#[Route('/api/indicateurs')]
class IndicateurController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * Taux de germination, survie, mortalite et temps de production par lot.
     */
    #[Route('/lots', name: 'indic_lots', methods: ['GET'])]
    public function parLot(): JsonResponse
    {
        $lots = $this->em->getRepository(Lot::class)->findAll();
        $result = [];

        foreach ($lots as $lot) {
            $leves = $lot->getNbPlantsLeves() ?? 0;
            $perdus = 0;
            foreach ($lot->getPertes() as $perte) {
                $perdus += $perte->getNbPlantsPerdus() ?? 0;
            }

            $tauxSurvie = $leves > 0 ? round(($leves - $perdus) * 100 / $leves, 2) : null;
            $tauxMortalite = $leves > 0 ? round($perdus * 100 / $leves, 2) : null;

            $tempsProduction = null;
            if ($lot->getDateSemis() && $lot->getDateSortie()) {
                $tempsProduction = $lot->getDateSemis()->diff($lot->getDateSortie())->days;
            }

            $result[] = [
                'idLot'                => $lot->getId(),
                'numeroLot'            => $lot->getNumeroLot(),
                'pepiniere'            => $lot->getPepiniere()?->getCodePepiniere(),
                'espece'               => $lot->getEspece()?->getNomCommun(),
                'nbGrainesSemees'      => $lot->getNbGrainesSemees(),
                'nbPlantsLeves'        => $leves,
                'tauxGermination'      => $lot->getTauxGermination(),
                'totalPerdus'          => $perdus,
                'tauxSurvie'           => $tauxSurvie,
                'tauxMortalite'        => $tauxMortalite,
                'tempsProductionJours' => $tempsProduction,
            ];
        }

        return $this->json($result);
    }

    /**
     * Tableau de bord : synthese par pepiniere et par espece.
     */
    #[Route('/tableau-bord', name: 'indic_tableau_bord', methods: ['GET'])]
    public function tableauBord(): JsonResponse
    {
        $rows = $this->em->createQuery(
            'SELECT p.codePepiniere AS code, p.nomPepiniere AS nom, e.nomCommun AS espece,
                    SUM(COALESCE(l.nbPlantsLeves, 0)) AS plantsProduits,
                    AVG(l.tauxGermination) AS tauxGerminationMoy
             FROM App\\Entity\\Lot l
             JOIN l.pepiniere p
             JOIN l.espece e
             GROUP BY p.codePepiniere, p.nomPepiniere, e.nomCommun'
        )->getResult();

        return $this->json($rows);
    }

    /**
     * Rendement de la pepiniere = plants distribues / capacite de production.
     */
    #[Route('/rendement', name: 'indic_rendement', methods: ['GET'])]
    public function rendement(): JsonResponse
    {
        $pepinieres = $this->em->getRepository(Pepiniere::class)->findAll();
        $result = [];

        foreach ($pepinieres as $pep) {
            $distribue = (int) $this->em->createQuery(
                'SELECT COALESCE(SUM(d.quantite), 0)
                 FROM App\\Entity\\Distribution d
                 JOIN d.lot l
                 WHERE l.pepiniere = :pep'
            )->setParameter('pep', $pep)->getSingleScalarResult();

            $capacite = $pep->getCapaciteProductionAn() ?? 0;
            $rendement = $capacite > 0 ? round($distribue * 100 / $capacite, 2) : null;

            $result[] = [
                'codePepiniere'         => $pep->getCodePepiniere(),
                'nomPepiniere'          => $pep->getNomPepiniere(),
                'capaciteProductionAn'  => $capacite,
                'totalDistribue'        => $distribue,
                'rendementPct'          => $rendement,
            ];
        }

        return $this->json($result);
    }
}
