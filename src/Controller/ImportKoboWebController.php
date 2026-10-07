<?php

namespace App\Controller;

use App\Service\KoboImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/import')]
class ImportKoboWebController extends AbstractController
{
    private const SESSION_RAPPORT = 'kobo_rapport_anomalies';

    public function __construct(private readonly KoboImporter $importer)
    {
    }

    #[Route('', name: 'import_kobo', methods: ['GET', 'POST'])]
    public function import(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $resume = null;

        if ($request->isMethod('POST')) {
            // Protection CSRF
            if (!$this->isCsrfTokenValid('import_kobo', (string) $request->request->get('_token'))) {
                $this->addFlash('error', 'Jeton de sécurité invalide, veuillez réessayer.');
                return $this->redirectToRoute('import_kobo');
            }

            /** @var UploadedFile|null $fichier */
            $fichier = $request->files->get('fichier');
            $dryRun = (bool) $request->request->get('dry_run');

            if (!$fichier instanceof UploadedFile) {
                $this->addFlash('error', 'Veuillez sélectionner un fichier à importer.');
                return $this->redirectToRoute('import_kobo');
            }

            $extension = strtolower($fichier->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls'], true)) {
                $this->addFlash('error', 'Format non pris en charge. Importez un fichier Excel (.xlsx) exporté depuis Kobo.');
                return $this->redirectToRoute('import_kobo');
            }

            try {
                $chemin = $fichier->getRealPath() ?: $fichier->getPathname();
                $resume = $this->importer->import($chemin, $dryRun);

                // Mémorise le rapport d'anomalies pour permettre son téléchargement.
                $request->getSession()->set(self::SESSION_RAPPORT, $resume['anomalies']);

                $message = sprintf(
                    '%s : %d pépinière(s) et %d lot(s)/espèce(s) %s. %d bloqué(s), %d corrigé(s), %d avertissement(s).',
                    $resume['dryRun'] ? 'Simulation effectuée' : 'Import terminé',
                    $resume['pepinieres'],
                    $resume['lots'],
                    $resume['dryRun'] ? 'détectés (aucune écriture)' : 'enregistrés',
                    $resume['bloques'],
                    $resume['corriges'],
                    $resume['avertissements']
                );
                $this->addFlash($resume['dryRun'] ? 'info' : 'success', $message);
            } catch (\Throwable $e) {
                $this->addFlash('error', "Échec de l'import : " . $e->getMessage());
            }
        }

        return $this->render('import/index.html.twig', [
            'resume' => $resume,
        ]);
    }

    #[Route('/rapport.csv', name: 'import_kobo_rapport', methods: ['GET'])]
    public function rapport(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $anomalies = $request->getSession()->get(self::SESSION_RAPPORT, []);

        $csv = "\xEF\xBB\xBF"; // BOM UTF-8 pour Excel
        $sep = ';';
        $csv .= implode($sep, ['Ligne Kobo', 'Section', 'Identifiant', 'Sévérité', 'Message']) . "\r\n";
        foreach ($anomalies as $a) {
            $ligne = [
                $a['ligne'] ?? '',
                $a['section'] ?? '',
                $a['identifiant'] ?? '',
                $a['severite'] ?? '',
                $a['message'] ?? '',
            ];
            $ligne = array_map(static function ($c) {
                $c = str_replace('"', '""', (string) $c);
                return '"' . $c . '"';
            }, $ligne);
            $csv .= implode($sep, $ligne) . "\r\n";
        }

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="rapport_import_kobo_' . date('Ymd_His') . '.csv"'
        );
        return $response;
    }
}
