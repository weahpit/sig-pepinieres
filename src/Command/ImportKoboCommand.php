<?php

namespace App\Command;

use App\Service\KoboImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe un export KoboToolbox « Suivi de la production des plants » (.xlsx)
 * dans les entités de l'application pépinière (version ligne de commande).
 *
 * Prérequis : composer require phpoffice/phpspreadsheet
 *
 * Usage :
 *   php bin/console app:import:kobo var/export.xlsx --dry-run
 *   php bin/console app:import:kobo var/export.xlsx
 */
#[AsCommand(name: 'app:import:kobo', description: 'Importe un export KoboToolbox dans les pépinières')]
class ImportKoboCommand extends Command
{
    public function __construct(private readonly KoboImporter $importer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('fichier', InputArgument::REQUIRED, 'Chemin du .xlsx exporté depuis Kobo')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Analyse sans écrire en base')
            ->addOption('rapport', null, InputOption::VALUE_REQUIRED, 'Chemin du fichier CSV où écrire le rapport des anomalies');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fichier = $input->getArgument('fichier');
        $dryRun = (bool) $input->getOption('dry-run');

        try {
            $r = $this->importer->import($fichier, $dryRun);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success(sprintf(
            '%s : %d pépinière(s) et %d lot(s)/espèce(s) %s.',
            $r['dryRun'] ? 'Simulation' : 'Import terminé',
            $r['pepinieres'],
            $r['lots'],
            $r['dryRun'] ? 'détectés' : 'enregistrés'
        ));

        $io->writeln(sprintf(
            '  Anomalies : %d bloqué(s), %d corrigé(s), %d avertissement(s).',
            $r['bloques'],
            $r['corriges'],
            $r['avertissements']
        ));

        if (!empty($r['anomalies'])) {
            $io->table(
                ['Ligne', 'Section', 'Identifiant', 'Sévérité', 'Message'],
                array_map(static fn ($a) => [
                    $a['ligne'], $a['section'], $a['identifiant'], $a['severite'], $a['message'],
                ], $r['anomalies'])
            );
        }

        $rapport = $input->getOption('rapport');
        if ($rapport) {
            $csv = "\xEF\xBB\xBF" . "Ligne;Section;Identifiant;Sévérité;Message\r\n";
            foreach ($r['anomalies'] as $a) {
                $cells = array_map(
                    static fn ($c) => '"' . str_replace('"', '""', (string) $c) . '"',
                    [$a['ligne'], $a['section'], $a['identifiant'], $a['severite'], $a['message']]
                );
                $csv .= implode(';', $cells) . "\r\n";
            }
            file_put_contents($rapport, $csv);
            $io->writeln("  Rapport CSV écrit : $rapport");
        }

        return Command::SUCCESS;
    }
}
