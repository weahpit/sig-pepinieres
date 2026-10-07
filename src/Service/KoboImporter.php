<?php

namespace App\Service;

use App\Entity\Espece;
use App\Entity\Lot;
use App\Entity\Pepiniere;
use App\Entity\SuiviCroissance;
use App\Entity\SuiviPerte;
use App\Entity\SuiviPhytosanitaire;
use App\Enum\CausePerte;
use App\Enum\EtatSanitaire;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Importe un export KoboToolbox « Suivi de la production des plants » (.xlsx)
 * dans les entités de l'application pépinière.
 *
 * Prérequis : composer require phpoffice/phpspreadsheet
 *
 * Logique partagée entre la commande console (app:import:kobo)
 * et la page web d'import (/import).
 */
class KoboImporter
{
    /** État phytosanitaire Kobo -> enum EtatSanitaire. */
    private const ETAT_MAP = [
        'Bon'          => 'Sain',
        'Moyen'        => 'Faible',
        'Mauvais'      => 'Malade',
        'Très mauvais' => 'Mort',
    ];

    private bool $dryRun = false;

    /** Anomalies collectées pendant l'import (rapport des lignes en erreur). */
    private array $anomalies = [];

    /** Clés (code pépinière|espèce) déjà vues dans le fichier -> détection des doublons. */
    private array $lotsVus = [];

    /** Codes de pépinière déjà vus dans le fichier. */
    private array $pepinieresVues = [];

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * Importe le fichier et retourne un résumé.
     *
     * @return array{pepinieres:int, lots:int, dryRun:bool, bloques:int, corriges:int, avertissements:int, anomalies:array}
     */
    public function import(string $fichier, bool $dryRun = false): array
    {
        if (!is_file($fichier)) {
            throw new \RuntimeException("Fichier introuvable : $fichier");
        }
        $this->dryRun = $dryRun;
        $this->anomalies = [];
        $this->lotsVus = [];
        $this->pepinieresVues = [];

        $spreadsheet = IOFactory::load($fichier);
        $principale = $spreadsheet->getSheet(0);
        $repEspeces = $this->feuilleParNom($spreadsheet, 'rep_especes');

        $soumissions = $this->lignesIndexees($principale);
        $especes = $repEspeces ? $this->lignesIndexees($repEspeces) : [];

        $especesParParent = [];
        foreach ($especes as $e) {
            $parent = $e['_parent_index'] ?? null;
            if ($parent !== null && $parent !== '') {
                $especesParParent[(string) $parent][] = $e;
            }
        }

        $nbPep = 0;
        $nbLots = 0;
        foreach ($soumissions as $s) {
            if ($this->val($s, '_index') === null) {
                continue;
            }
            $pep = $this->importerPepiniere($s);
            if ($pep === null) {
                continue;
            }
            ++$nbPep;
            foreach ($especesParParent[(string) $s['_index']] ?? [] as $e) {
                if ($this->importerEspece($pep, $s, $e)) {
                    ++$nbLots;
                }
            }
        }

        if (!$this->dryRun) {
            $this->em->flush();
        }

        $compte = fn (string $sev) => count(array_filter(
            $this->anomalies,
            static fn ($a) => $a['severite'] === $sev
        ));

        return [
            'pepinieres'     => $nbPep,
            'lots'           => $nbLots,
            'dryRun'         => $this->dryRun,
            'bloques'        => $compte('bloquant'),
            'corriges'       => $compte('corrige'),
            'avertissements' => $compte('avertissement'),
            'anomalies'      => $this->anomalies,
        ];
    }

    /** @return array accessible après import() pour générer un rapport. */
    public function getAnomalies(): array
    {
        return $this->anomalies;
    }

    /**
     * Enregistre une anomalie dans le rapport.
     *
     * @param string $severite bloquant | corrige | avertissement
     */
    private function ajouterAnomalie(
        string $ligne,
        string $section,
        string $identifiant,
        string $severite,
        string $message
    ): void {
        $this->anomalies[] = [
            'ligne'       => $ligne,
            'section'     => $section,
            'identifiant' => $identifiant,
            'severite'    => $severite,
            'message'     => $message,
        ];
    }

    // ---------------------------------------------------------------
    //  Lecture du classeur
    // ---------------------------------------------------------------

    private function feuilleParNom($spreadsheet, string $prefixe)
    {
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if (str_starts_with($sheet->getTitle(), $prefixe)) {
                return $sheet;
            }
        }
        return null;
    }

    /** @return array<int, array<string, mixed>> */
    private function lignesIndexees($sheet): array
    {
        $rows = $sheet->toArray(null, true, true, false);
        if (!$rows) {
            return [];
        }
        $entetes = array_map(static fn ($h) => (string) $h, $rows[0]);
        $out = [];
        foreach (array_slice($rows, 1) as $r) {
            $assoc = [];
            foreach ($entetes as $i => $nom) {
                $assoc[$nom] = $r[$i] ?? null;
            }
            $out[] = $assoc;
        }
        return $out;
    }

    private function val(array $row, string $cle, $defaut = null)
    {
        $v = $row[$cle] ?? null;
        return ($v === null || $v === '') ? $defaut : $v;
    }

    private function toStr($v): ?string
    {
        return ($v === null || $v === '') ? null : (string) $v;
    }

    private function toInt($v): ?int
    {
        return ($v === null || $v === '') ? null : (int) round((float) $v);
    }

    private function toDate($v): ?\DateTimeImmutable
    {
        if ($v instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($v);
        }
        if (is_numeric($v)) {
            try {
                return \DateTimeImmutable::createFromMutable(ExcelDate::excelToDateTimeObject((float) $v));
            } catch (\Throwable) {
                return null;
            }
        }
        if (is_string($v) && $v !== '') {
            try {
                return new \DateTimeImmutable($v);
            } catch (\Throwable) {
                return null;
            }
        }
        return null;
    }

    // ---------------------------------------------------------------
    //  Mapping métier
    // ---------------------------------------------------------------

    private function importerPepiniere(array $s): ?Pepiniere
    {
        $ligne = (string) ($s['_index'] ?? '?');
        $code = (string) ($this->val($s, 'Code du site')
            ?? $this->val($s, 'code_pepinieriste')
            ?? ('KOBO-' . $s['_index']));

        // Doublon dans le fichier : plusieurs soumissions pour le même site.
        if (isset($this->pepinieresVues[$code])) {
            $this->ajouterAnomalie(
                $ligne,
                'Pépinière',
                $code,
                'bloquant',
                "Code de site en double dans le fichier (ligne #{$this->pepinieresVues[$code]} déjà traitée) : soumission ignorée."
            );
            return null;
        }
        $this->pepinieresVues[$code] = $ligne;

        $pep = $this->em->getRepository(Pepiniere::class)->findOneBy(['codePepiniere' => $code]) ?? new Pepiniere();

        $nom = $this->val($s, 'Préciser le nom du pépiniériste')
            ?? $this->val($s, 'Pépiniériste / agri-entrepreneur')
            ?? ('Pépinière ' . $code);

        // Nettoyage des coordonnées GPS.
        $lat = $this->nettoyerCoordonnee($this->val($s, '_GPS du centroïde de la pépinière_latitude'), -90, 90);
        $lon = $this->nettoyerCoordonnee($this->val($s, '_GPS du centroïde de la pépinière_longitude'), -180, 180);
        if ($lat === null && $this->val($s, '_GPS du centroïde de la pépinière_latitude') !== null) {
            $this->ajouterAnomalie($ligne, 'Pépinière', $code, 'corrige', 'Latitude invalide ignorée.');
        }
        if ($lon === null && $this->val($s, '_GPS du centroïde de la pépinière_longitude') !== null) {
            $this->ajouterAnomalie($ligne, 'Pépinière', $code, 'corrige', 'Longitude invalide ignorée.');
        }

        $pep->setCodePepiniere($code);
        $pep->setNomPepiniere((string) $nom);
        $pep->setRegion($this->val($s, 'Délégation régionale'));
        $pep->setDepartement($this->val($s, 'Département'));
        $pep->setVillage($this->val($s, 'Site de la pépinière (village / quartier / précision)'));
        $pep->setLatitude($lat !== null ? (string) $lat : null);
        $pep->setLongitude($lon !== null ? (string) $lon : null);
        $contour = $this->parseContour($this->val($s, 'Délimitation de la surface de la pépinière'));
        $pep->setContourGeojson($contour);
        $pep->setSuperficieM2($this->toStr($this->polygonAreaM2($contour)));
        $pep->setOrganismeGestionnaire($this->val($s, 'Préciser le nom du pépiniériste'));
        $pep->setDateCreation($this->toDate($this->val($s, 'Date de visite')));
        $pep->setCapaciteProductionAn($this->toInt($this->val($s, 'Capacité du site (nombre de plants)')));
        $pep->setDateMaj(new \DateTimeImmutable());

        if (!$this->dryRun) {
            $this->em->persist($pep);
        }
        return $pep;
    }

    private function importerEspece(Pepiniere $pep, array $s, array $e): bool
    {
        $ligne = (string) ($e['_index'] ?? '?');
        $nomEspece = $this->val($e, 'Espèce forestière');
        if ($nomEspece === 'Autre') {
            $nomEspece = $this->val($e, 'Préciser l’espèce') ?? 'Autre';
        }

        // Comptages bruts (pour détecter les lignes vides et incohérentes).
        $initial = $this->toInt($this->val($e, 'total_initial'))
            ?? $this->toInt($this->val($e, 'Nombre de plants issus de graines'));
        $vivants = $this->toInt($this->val($e, 'Nombre de plants vivants'));
        $morts = $this->toInt($this->val($e, 'Nombre de plants morts'));

        // Ligne « Autre » entièrement vide (gabarit non rempli) -> ignorer sans erreur.
        $toutVide = ($initial ?? 0) === 0 && ($vivants ?? 0) === 0 && ($morts ?? 0) === 0;
        if (($nomEspece === null || $nomEspece === 'Autre') && $toutVide) {
            return false;
        }
        if ($nomEspece === null) {
            $this->ajouterAnomalie($ligne, 'Espèce', $pep->getCodePepiniere(), 'bloquant',
                'Espèce non renseignée : lot ignoré.');
            return false;
        }

        // Blocage des doublons (même pépinière + même espèce dans le fichier).
        $cle = $pep->getCodePepiniere() . '|' . $nomEspece;
        if (isset($this->lotsVus[$cle])) {
            $this->ajouterAnomalie($ligne, 'Espèce', $pep->getCodePepiniere() . ' / ' . $nomEspece, 'bloquant',
                "Espèce en double pour cette pépinière (ligne #{$this->lotsVus[$cle]} déjà traitée) : lot ignoré.");
            return false;
        }
        $this->lotsVus[$cle] = $ligne;

        // Nettoyage : valeurs négatives ramenées à 0.
        $ident = $pep->getCodePepiniere() . ' / ' . $nomEspece;
        if ($initial !== null && $initial < 0) {
            $this->ajouterAnomalie($ligne, 'Espèce', $ident, 'corrige', 'Nombre initial négatif ramené à 0.');
            $initial = 0;
        }
        if ($vivants !== null && $vivants < 0) {
            $this->ajouterAnomalie($ligne, 'Espèce', $ident, 'corrige', 'Nombre de plants vivants négatif ramené à 0.');
            $vivants = 0;
        }
        if ($morts !== null && $morts < 0) {
            $this->ajouterAnomalie($ligne, 'Espèce', $ident, 'corrige', 'Nombre de plants morts négatif ramené à 0.');
            $morts = 0;
        }

        // Incohérence de terrain : plus de plants vivants que la base de production.
        if ($initial !== null && $initial > 0 && $vivants !== null && $vivants > $initial) {
            $this->ajouterAnomalie($ligne, 'Espèce', $ident, 'avertissement',
                "Plants vivants ($vivants) supérieurs au total initial ($initial) : donnée conservée telle quelle.");
        }

        // Dates incohérentes (repiquage avant semis).
        $dSemis = $this->toDate($this->val($e, 'Date de semis'));
        $dRepiquage = $this->toDate($this->val($e, 'Date de repiquage'));
        if ($dSemis && $dRepiquage && $dRepiquage < $dSemis) {
            $this->ajouterAnomalie($ligne, 'Espèce', $ident, 'avertissement',
                'Date de repiquage antérieure à la date de semis.');
        }

        $espece = $this->findOrCreateEspece((string) $nomEspece);
        $dateVisite = $this->toDate($this->val($s, 'Date de visite')) ?? new \DateTimeImmutable();

        // numero_lot unique par (pepiniere, numero_lot)
        $numero = mb_substr($pep->getCodePepiniere() . '-' . $nomEspece, 0, 80);
        $lot = $this->em->getRepository(Lot::class)
            ->findOneBy(['pepiniere' => $pep, 'numeroLot' => $numero]) ?? new Lot();
        $nouveau = $lot->getId() === null;

        $lot->setNumeroLot($numero);
        $lot->setPepiniere($pep);
        $lot->setEspece($espece);
        $lot->setDateSemis($dSemis);
        // Base de production (graines + stumps/sauvageons) ; sert de dénominateur
        // au taux de germination/reprise calculé par l'entité Lot.
        $lot->setNbGrainesSemees($initial);
        $lot->setNbPlantsLeves($vivants);
        if (!$this->dryRun) {
            $this->em->persist($lot);
        }

        // Les suivis ne sont créés qu'à la première importation du lot (idempotence).
        if (!$nouveau) {
            return true;
        }

        // Suivi de croissance (état sanitaire)
        $etatKobo = $this->val($e, 'État phytosanitaire général');
        $sc = new SuiviCroissance();
        $sc->setLot($lot);
        $sc->setDateMesure($dateVisite);
        if ($etatKobo !== null && isset(self::ETAT_MAP[$etatKobo])) {
            $sc->setEtatSanitaire(EtatSanitaire::from(self::ETAT_MAP[$etatKobo]));
        }
        $sc->setObservations($this->val($e, 'Observation complémentaire sur l’espèce'));
        if (!$this->dryRun) {
            $this->em->persist($sc);
        }

        // Pertes (plants morts)
        if ($morts !== null && $morts > 0) {
            $perte = new SuiviPerte();
            $perte->setLot($lot);
            $perte->setDatePerte($dateVisite);
            $perte->setCause(CausePerte::Mortalite);
            $perte->setNbPlantsPerdus($morts);
            if (!$this->dryRun) {
                $this->em->persist($perte);
            }
        }

        // Suivi phytosanitaire (si problème observé)
        if ($this->val($e, 'Problème phytosanitaire observé ?') === 'Oui') {
            $nature = $this->val($e, 'Nature du problème observé');
            if ($nature === null) {
                $this->ajouterAnomalie($ligne, 'Espèce', $ident, 'avertissement',
                    'Problème phytosanitaire signalé sans nature précisée.');
            }
            $sp = new SuiviPhytosanitaire();
            $sp->setLot($lot);
            $sp->setDateObservation($dateVisite);
            $sp->setMaladieRavageur($nature);
            $sp->setSymptomes($nature);
            if (!$this->dryRun) {
                $this->em->persist($sp);
            }
        }

        return true;
    }

    /** Valide une coordonnée GPS numérique dans l'intervalle attendu, sinon null. */
    private function nettoyerCoordonnee($v, float $min, float $max): ?float
    {
        if ($v === null || $v === '' || !is_numeric($v)) {
            return null;
        }
        $f = (float) $v;
        if ($f < $min || $f > $max || ($f == 0.0)) {
            return null;
        }
        return $f;
    }

    private function findOrCreateEspece(string $nom): Espece
    {
        $espece = $this->em->getRepository(Espece::class)->findOneBy(['nomCommun' => $nom]);
        if ($espece === null) {
            $espece = new Espece();
            $espece->setNomCommun($nom);
            if (!$this->dryRun) {
                $this->em->persist($espece);
            }
        }
        return $espece;
    }

    /**
     * Analyse la « Délimitation de la surface » Kobo (« lat lon alt prec;... »)
     * et retourne le contour sous forme de liste ordonnée de points [longitude, latitude]
     * (ordre GeoJSON), ou null si moins de 3 sommets exploitables.
     *
     * @return array<int, array{0: float, 1: float}>|null
     */
    private function parseContour($delim): ?array
    {
        if (!$delim) {
            return null;
        }
        $ring = [];
        foreach (explode(';', (string) $delim) as $part) {
            $toks = preg_split('/\\s+/', trim($part));
            if (count($toks) >= 2 && is_numeric($toks[0]) && is_numeric($toks[1])) {
                $lat = (float) $toks[0];
                $lon = (float) $toks[1];
                if ($lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
                    $ring[] = [$lon, $lat]; // GeoJSON = [lon, lat]
                }
            }
        }
        return count($ring) >= 3 ? $ring : null;
    }

    /**
     * Aire approximative (m²) d'un contour [lon, lat] (formule du lacet, projection
     * équirectangulaire centrée sur la latitude moyenne).
     *
     * @param array<int, array{0: float, 1: float}>|null $ring
     */
    private function polygonAreaM2(?array $ring): ?float
    {
        if (!$ring || count($ring) < 3) {
            return null;
        }
        $lat0 = array_sum(array_column($ring, 1)) / count($ring);
        $r = 6378137.0;
        $xy = [];
        foreach ($ring as [$lon, $lat]) {
            $xy[] = [deg2rad($lon) * $r * cos(deg2rad($lat0)), deg2rad($lat) * $r];
        }
        $area = 0.0;
        $n = count($xy);
        for ($i = 0; $i < $n; ++$i) {
            [$x1, $y1] = $xy[$i];
            [$x2, $y2] = $xy[($i + 1) % $n];
            $area += $x1 * $y2 - $x2 * $y1;
        }
        return round(abs($area) / 2.0, 2);
    }
}
