<?php

namespace App\Command;

use App\Service\DataImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import:movies',
    description: 'Import movies from CSV file',
)]
class ImportMoviesCommand extends Command
{
    public function __construct(
        private DataImportService $importService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('csv-file', InputArgument::REQUIRED, 'Path to the CSV file to import')
            ->setHelp('This command allows you to import movies from a CSV file.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csvFile = $input->getArgument('csv-file');

        $io->title('Import des films depuis le fichier CSV');
        $io->info("Fichier : {$csvFile}");

        try {
            $result = $this->importService->importMoviesFromCsv($csvFile, $output);
            
            $io->newLine();
            $io->success('Import terminé !');
            
            $io->section('Statistiques');
            $io->table(
                ['Métrique', 'Valeur'],
                [
                    ['Films importés', $result['stats']['imported']],
                    ['Erreurs', $result['stats']['errors']],
                    ['Ignorés', $result['stats']['skipped']],
                ]
            );

            if (!empty($result['errors'])) {
                $io->section('Erreurs rencontrées');
                foreach ($result['errors'] as $error) {
                    $io->error($error);
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'import : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}