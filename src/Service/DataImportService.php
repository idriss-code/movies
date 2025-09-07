<?php

namespace App\Service;

use App\Entity\Actor;
use App\Entity\Director;
use App\Entity\Movie;
use App\Entity\Studio;
use App\Entity\Tag;
use App\Repository\ActorRepository;
use App\Repository\DirectorRepository;
use App\Repository\MovieRepository;
use App\Repository\StudioRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class DataImportService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MovieRepository $movieRepository,
        private StudioRepository $studioRepository,
        private DirectorRepository $directorRepository,
        private ActorRepository $actorRepository,
        private TagRepository $tagRepository
    ) {}

    public function importMoviesFromCsv(string $csvFilePath, OutputInterface $output): array
    {
        if (!file_exists($csvFilePath)) {
            throw new \InvalidArgumentException("Le fichier CSV n'existe pas : {$csvFilePath}");
        }

        $csv = Reader::createFromPath($csvFilePath, 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        $stats = ['imported' => 0, 'updated' => 0, 'errors' => 0, 'skipped' => 0];
        $errors = [];

        $recordsArray = iterator_to_array($records);
        $progressBar = new ProgressBar($output, count($recordsArray));
        $progressBar->start();

        foreach ($recordsArray as $index => $record) {
            try {
                $this->processMovieRecord($record, $stats);
                $progressBar->advance();
            } catch (\Exception $e) {
                $stats['errors']++;
                $lineNumber = $index + 2; // +2 car index commence à 0 et on a un header
                $movieTitle = $record['title'] ?? 'INCONNU';
                $studioName = $record['studio_name'] ?? 'INCONNU';
                
                $errorMsg = "Ligne {$lineNumber} - Film: '{$movieTitle}' (Studio: '{$studioName}') - Erreur: " . $e->getMessage();
                $errors[] = $errorMsg;
                
                $output->writeln("\n[ERREUR] " . $errorMsg);
                
                // Afficher les données de la ligne problématique
                $output->writeln("Données de la ligne:");
                foreach ($record as $key => $value) {
                    $displayValue = $value ?? 'NULL';
                    if (strlen($displayValue) > 80) {
                        $displayValue = substr($displayValue, 0, 80) . '...';
                    }
                    $output->writeln("  {$key}: {$displayValue}");
                }
                $output->writeln("");
                
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        
        // Flush final avec gestion d'erreur
        try {
            if ($this->entityManager->isOpen()) {
                $this->entityManager->flush();
            } else {
                $output->writeln("\n[WARNING] EntityManager était déjà fermé avant le flush final");
            }
        } catch (\Exception $e) {
            $output->writeln("\n[ERROR] Erreur lors du flush final: " . $e->getMessage());
            $errors[] = "Erreur flush final: " . $e->getMessage();
        }

        return ['stats' => $stats, 'errors' => $errors];
    }

    private function processMovieRecord(array $record, array &$stats): void
    {
        if (empty($record['title'])) {
            throw new \InvalidArgumentException('Le titre du film est requis');
        }
        
        // Vérifier la longueur du download_link
        $downloadLink = $record['download_link'] ?? '';
        if (strlen($downloadLink) > 2048) {
            $stats['skipped']++;
            throw new \InvalidArgumentException("Film '{$record['title']}' ignoré : download_link trop long (" . strlen($downloadLink) . " caractères, limite 2048)");
        }

        $studioName = !empty($record['studio_name']) ? $record['studio_name'] : null;
        $format = $record['format'] ?? null;
        
        // Vérifier si un film avec le même titre, studio et format existe déjà
        $existingMovie = $this->movieRepository->findByTitleStudioNameAndFormat($record['title'], $studioName, $format);
        
        // Détecter et logger les doublons potentiels
        $this->checkForDuplicates($record['title'], $studioName, $format);
        
        $studio = null;
        if ($studioName) {
            $studio = $this->findOrCreateStudio($studioName, $record['studio_logo_url'] ?? null);
        }
        
        if ($existingMovie) {
            // Mettre à jour le film existant
            $movie = $existingMovie;
            $stats['updated']++;
        } else {
            // Créer un nouveau film
            $movie = new Movie();
            $stats['imported']++;
        }

        $director = null;
        if (!empty($record['director_firstname']) && !empty($record['director_lastname'])) {
            $director = $this->findOrCreateDirector($record['director_firstname'], $record['director_lastname']);
        }

        $movie->setTitle($record['title'])
            ->setYear((int) $record['year'])
            ->setPoster($record['poster_url'] ?? $record['poster'] ?? null)
            ->setStudio($studio)
            ->setDirector($director)
            ->setDownloadLink($record['download_link'] ?? null)
            ->setFormat($format)
            ->setFileSize($record['file_size'] ?? null)
            ->setDuration($record['duration'] ?? null);

        if (!empty($record['added_at'])) {
            $movie->setAddedAt(new \DateTime($record['added_at']));
        }

        // Réinitialiser les acteurs et tags pour la mise à jour
        if ($existingMovie) {
            $movie->getActors()->clear();
            $movie->getTags()->clear();
        }

        if (!empty($record['actors'])) {
            $actorNames = explode('|', $record['actors']);
            foreach ($actorNames as $actorName) {
                $names = explode(' ', trim($actorName), 2);
                if (count($names) === 2) {
                    $actor = $this->findOrCreateActor($names[0], $names[1]);
                    $movie->addActor($actor);
                }
            }
        }

        if (!empty($record['tags'])) {
            $tagNames = explode('|', $record['tags']);
            foreach ($tagNames as $tagName) {
                $tag = $this->findOrCreateTag(trim($tagName));
                $movie->addTag($tag);
            }
        }

        if (!$existingMovie) {
            $this->entityManager->persist($movie);
        }
    }

    public function importStudiosFromCsv(string $csvFilePath, OutputInterface $output): array
    {
        if (!file_exists($csvFilePath)) {
            throw new \InvalidArgumentException("Le fichier CSV n'existe pas : {$csvFilePath}");
        }

        $csv = Reader::createFromPath($csvFilePath, 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        $stats = ['imported' => 0, 'errors' => 0, 'skipped' => 0];
        $errors = [];

        $recordsArray = iterator_to_array($records);
        $progressBar = new ProgressBar($output, count($recordsArray));
        $progressBar->start();

        foreach ($recordsArray as $record) {
            try {
                if (empty($record['name'])) {
                    throw new \InvalidArgumentException('Le nom du studio est requis');
                }

                $existingStudio = $this->studioRepository->findOneByName($record['name']);
                if ($existingStudio) {
                    $stats['skipped']++;
                } else {
                    $studio = new Studio();
                    $studio->setName($record['name'])
                        ->setLogo($record['logo_url'] ?? null);

                    $this->entityManager->persist($studio);
                    $stats['imported']++;
                }
                $progressBar->advance();
            } catch (\Exception $e) {
                $stats['errors']++;
                $errors[] = "Erreur ligne {$progressBar->getProgress()}: " . $e->getMessage();
            }
        }

        $progressBar->finish();
        $this->entityManager->flush();

        return ['stats' => $stats, 'errors' => $errors];
    }

    private function findOrCreateStudio(string $name, ?string $logoUrl = null): Studio
    {
        // Recherche avec findBy pour éviter les exceptions sur les doublons
        $studios = $this->studioRepository->findBy(['name' => $name], ['id' => 'ASC'], 1);
        $studio = $studios ? $studios[0] : null;
        
        if (!$studio) {
            $studio = new Studio();
            $studio->setName($name)->setLogo($logoUrl);
            $this->entityManager->persist($studio);
            $this->entityManager->flush(); // Flush immédiat pour éviter les doublons
        }
        return $studio;
    }

    private function findOrCreateDirector(string $firstName, string $lastName): Director
    {
        // Recherche avec findBy pour éviter les exceptions sur les doublons
        $directors = $this->directorRepository->findBy(['firstName' => $firstName, 'lastName' => $lastName], ['id' => 'ASC'], 1);
        $director = $directors ? $directors[0] : null;
        
        if (!$director) {
            $director = new Director();
            $director->setFirstName($firstName)->setLastName($lastName);
            $this->entityManager->persist($director);
            $this->entityManager->flush(); // Flush immédiat pour éviter les doublons
        }
        return $director;
    }

    private function findOrCreateActor(string $firstName, string $lastName): Actor
    {
        // Recherche avec findBy pour éviter les exceptions sur les doublons
        $actors = $this->actorRepository->findBy(['firstName' => $firstName, 'lastName' => $lastName], ['id' => 'ASC'], 1);
        $actor = $actors ? $actors[0] : null;
        
        if (!$actor) {
            $actor = new Actor();
            $actor->setFirstName($firstName)->setLastName($lastName);
            $this->entityManager->persist($actor);
            $this->entityManager->flush(); // Flush immédiat pour éviter les doublons
        }
        return $actor;
    }

    private function findOrCreateTag(string $name): Tag
    {
        // Recherche avec findBy pour éviter les exceptions sur les doublons
        $tags = $this->tagRepository->findBy(['name' => $name], ['id' => 'ASC'], 1);
        $tag = $tags ? $tags[0] : null;
        
        if (!$tag) {
            $colors = ['#dc3545', '#198754', '#0d6efd', '#fd7e14', '#6f42c1', '#20c997', '#ffc107'];
            $tag = new Tag();
            $tag->setName($name)->setColor($colors[array_rand($colors)]);
            $this->entityManager->persist($tag);
            $this->entityManager->flush(); // Flush immédiat pour éviter les doublons
        }
        return $tag;
    }
    
    private function checkForDuplicates(string $title, ?string $studioName, ?string $format): void
    {
        // Créer une requête pour compter les films avec les mêmes critères
        $queryBuilder = $this->movieRepository->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->leftJoin('m.studio', 's')
            ->where('m.title = :title')
            ->setParameter('title', $title);

        if ($studioName) {
            $queryBuilder->andWhere('s.name = :studio_name')
                ->setParameter('studio_name', $studioName);
        } else {
            $queryBuilder->andWhere('m.studio IS NULL');
        }

        if ($format) {
            $queryBuilder->andWhere('m.format = :format')
                ->setParameter('format', $format);
        } else {
            $queryBuilder->andWhere('m.format IS NULL');
        }

        $count = $queryBuilder->getQuery()->getSingleScalarResult();
        
        if ($count > 1) {
            error_log("[DUPLICATE DETECTED] Film: '{$title}' (Studio: '{$studioName}', Format: '{$format}') - {$count} exemplaires trouvés en base");
        }
    }
}