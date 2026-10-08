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

        // --- Données pour graphiques / alertes ---
        $etatCounts   = [];   // répartition par état sanitaire (dernier suivi par lot)
        $especeLeves  = [];   // plants levés par espèce
        $lotsCritiques   = []; // mortalité élevée
        $lotsSurveiller  = []; // germination faible
        $lotsSansSuivi   = 0;  // lots sans suivi sanitaire

        foreach ($lots as $lot) {
            $leves = $lot->getNbPlantsLeves() ?? 0;
            $perdus = 0;
            foreach ($lot->getPertes() as $perte) {
                $perdus += $perte->getNbPlantsPerdus() ?? 0;
            }
            $totalGraines += $lot->getNbGrainesSemees() ?? 0;
            $totalLeves += $leves;
            $totalPerdus += $perdus;

            $tauxSurvie    = $leves > 0 ? round(($leves - $perdus) * 100 / $leves, 1) : null;
            $tauxMortalite = $leves > 0 ? round($perdus * 100 / $leves, 1) : null;
            $germ          = $lot->getTauxGermination() !== null ? (float) $lot->getTauxGermination() : null;

            $indicateurs[] = [
                'lot'             => $lot,
                'tauxSurvie'      => $tauxSurvie,
                'tauxMortalite'   => $tauxMortalite,
                'perdus'          => $perdus,
            ];

            // Plants levés par espèce
            $esp = $lot->getEspece() ? $lot->getEspece()->getNomCommun() : 'Non renseignée';
            $especeLeves[$esp] = ($especeLeves[$esp] ?? 0) + (int) $leves;

            // État sanitaire = dernier suivi de croissance renseigné
            $etat = null; $ref = null;
            foreach ($lot->getSuivisCroissance() as $sc) {
                $e = $sc->getEtatSanitaire();
                $d = $sc->getDateMesure();
                if ($e === null || $d === null) { continue; }
                if ($ref === null || $d > $ref) { $ref = $d; $etat = $e->value; }
            }
            if ($etat !== null) {
                $etatCounts[$etat] = ($etatCounts[$etat] ?? 0) + 1;
            } else {
                $lotsSansSuivi++;
            }

            // Alertes
            if ($tauxMortalite !== null && $tauxMortalite >= 30) {
                $lotsCritiques[] = ['lot' => $lot, 'valeur' => $tauxMortalite];
            }
            if ($germ !== null && $germ < 50) {
                $lotsSurveiller[] = ['lot' => $lot, 'valeur' => $germ];
            }
        }

        // Tri des alertes (plus critique d'abord) et top espèces
        usort($lotsCritiques, fn($a, $b) => $b['valeur'] <=> $a['valeur']);
        usort($lotsSurveiller, fn($a, $b) => $a['valeur'] <=> $b['valeur']);
        arsort($especeLeves);
        $topEspeces = array_slice($especeLeves, 0, 6, true);

        // États sanitaires dégradés (hors « Sain »)
        $nbEtatDegrade = 0;
        foreach ($etatCounts as $k => $v) {
            if (!in_array($k, ['Sain', 'Bon', 'Bonne'], true)) { $nbEtatDegrade += $v; }
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
            // Graphiques
            'etatCounts'      => $etatCounts,
            'topEspeces'      => $topEspeces,
            // Alertes
            'lotsCritiques'   => $lotsCritiques,
            'lotsSurveiller'  => $lotsSurveiller,
            'lotsSansSuivi'   => $lotsSansSuivi,
            'nbEtatDegrade'   => $nbEtatDegrade,
        ]);
    }
}
