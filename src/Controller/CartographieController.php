<?php

namespace App\Controller;

use App\Entity\Pepiniere;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Cartographie des pépinières : page Leaflet + flux GeoJSON.
 */
#[Route('/carte')]
class CartographieController extends AbstractController
{
    public function __construct(private  EntityManagerInterface $em)
    {
    }

    /**
     * Page cartographique interactive.
     */
    #[Route('', name: 'carte_index', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->getUser()){return $this->redirectToRoute("app_login");}
        return $this->render('cartographie/index.html.twig');
    }

    /**
     * Couche des pépinières au format GeoJSON (FeatureCollection de points).
     * Seules les pépinières géoréférencées (lat/lon non nulles) sont exposées.
     */
    #[Route('/pepinieres.geojson', name: 'carte_pepinieres_geojson', methods: ['GET'])]
    public function geojson(): JsonResponse
    {
        /** @var Pepiniere[] $pepinieres */
        $pepinieres = $this->em->getRepository(Pepiniere::class)->findAll();

        $features = [];
        foreach ($pepinieres as $p) {
            $lat = $p->getLatitude();
            $lon = $p->getLongitude();
            if ($lat === null || $lon === null) {
                continue;
            }

            // Comptage des lots + estimation du nombre de plants levés.
            $nbLots = $p->getLots()->count();
            $plantsLeves = 0;
            // État sanitaire de la pépinière = état du suivi de croissance le plus récent
            // (tous lots confondus). null si aucun suivi renseigné.
            $etatSanitaire = null;
            $dateEtatRef = null;
            foreach ($p->getLots() as $lot) {
                $plantsLeves += (int) ($lot->getNbPlantsLeves() ?? 0);
                foreach ($lot->getSuivisCroissance() as $sc) {
                    $etat = $sc->getEtatSanitaire();
                    $date = $sc->getDateMesure();
                    if ($etat === null || $date === null) {
                        continue;
                    }
                    if ($dateEtatRef === null || $date > $dateEtatRef) {
                        $dateEtatRef = $date;
                        $etatSanitaire = $etat->value;
                    }
                }
            }

            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    // GeoJSON = [longitude, latitude]
                    'coordinates' => [(float) $lon, (float) $lat],
                ],
                'properties' => [
                    'id'            => $p->getId(),
                    'code'          => $p->getCodePepiniere(),
                    'nom'           => $p->getNomPepiniere(),
                    'region'        => $p->getRegion(),
                    'departement'   => $p->getDepartement(),
                    'sousPrefecture'=> $p->getSousPrefecture(),
                    'village'       => $p->getVillage(),
                    'superficieM2'  => $p->getSuperficieM2() !== null ? (float) $p->getSuperficieM2() : null,
                    'capaciteAn'    => $p->getCapaciteProductionAn(),
                    'organisme'     => $p->getOrganismeGestionnaire(),
                    'responsable'   => $p->getResponsable() ? (string) $p->getResponsable() : null,
                    'nbLots'        => $nbLots,
                    'plantsLeves'   => $plantsLeves,
                    'etatSanitaire' => $etatSanitaire,
                    'urlFiche'      => $this->generateUrl('pepiniere_show_web', ['id' => $p->getId()]),
                ],
            ];
        }

        return new JsonResponse([
            'type'     => 'FeatureCollection',
            'features' => $features,
            'meta'     => [
                'total'          => count($features),
                'genereLe'       => (new \DateTimeImmutable())->format(DATE_ATOM),
            ],
        ]);
    }
}
