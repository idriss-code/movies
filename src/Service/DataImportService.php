<?php

namespace App\Service;

use App\Entity\Actor;
use App\Entity\Director;
use App\Entity\Movie;
use App\Entity\Studio;
use App\Entity\Tag;
use App\Repository\ActorRepository;
use App\Repository\DirectorRepository;
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

        $stats = ['imported' => 0, 'errors' => 0, 'skipped' => 0];
        $errors = [];

        $recordsArray = iterator_to_array($records);
        $progressBar = new ProgressBar($output, count($recordsArray));
        $progressBar->start();

        foreach ($recordsArray as $record) {
            try {
                $this->processMovieRecord($record, $stats);
                $progressBar->advance();
            } catch (\Exception $e) {
                $stats['errors']++;
                $errors[] = "Erreur ligne {$progressBar->getProgress()}: " . $e->getMessage();
                $output->writeln("Erreur: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->entityManager->flush();

        return ['stats' => $stats, 'errors' => $errors];
    }

    private function processMovieRecord(array $record, array &$stats): void
    {
        if (empty($record['title'])) {
            throw new \InvalidArgumentException('Le titre du film est requis');
        }

        $studio = null;
        if (!empty($record['studio_name'])) {
            $studio = $this->findOrCreateStudio($record['studio_name'], $record['studio_logo_url'] ?? null);
        }

        $director = null;
        if (!empty($record['director_firstname']) && !empty($record['director_lastname'])) {
            $director = $this->findOrCreateDirector($record['director_firstname'], $record['director_lastname']);
        }

        $movie = new Movie();
        $movie->setTitle($record['title'])
            ->setYear((int) $record['year'])
            ->setPoster($record['poster_url'] ?? $record['poster'] ?? null)
            ->setStudio($studio)
            ->setDirector($director)
            ->setDownloadLink($record['download_link'] ?? null)
            ->setFormat($record['format'] ?? null)
            ->setFileSize($record['file_size'] ?? null)
            ->setDuration($record['duration'] ?? null);

        if (!empty($record['added_at'])) {
            $movie->setAddedAt(new \DateTime($record['added_at']));
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

        $this->entityManager->persist($movie);
        $stats['imported']++;
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
        try {
            $studio = $this->studioRepository->findOneByName($name);
        } catch (\Exception $e) {
            // Si plusieurs résultats, prendre le premier
            $studios = $this->studioRepository->findBy(['name' => $name], ['id' => 'ASC'], 1);
            $studio = $studios ? $studios[0] : null;
        }
        
        if (!$studio) {
            $studio = new Studio();
            $studio->setName($name)->setLogo($logoUrl);
            $this->entityManager->persist($studio);
        }
        return $studio;
    }

    private function findOrCreateDirector(string $firstName, string $lastName): Director
    {
        try {
            $director = $this->directorRepository->findOneByName($firstName, $lastName);
        } catch (\Exception $e) {
            // Si plusieurs résultats, prendre le premier
            $directors = $this->directorRepository->findBy(['firstName' => $firstName, 'lastName' => $lastName], ['id' => 'ASC'], 1);
            $director = $directors ? $directors[0] : null;
        }
        
        if (!$director) {
            $director = new Director();
            $director->setFirstName($firstName)->setLastName($lastName);
            $this->entityManager->persist($director);
        }
        return $director;
    }

    private function findOrCreateActor(string $firstName, string $lastName): Actor
    {
        try {
            $actor = $this->actorRepository->findOneByName($firstName, $lastName);
        } catch (\Exception $e) {
            // Si plusieurs résultats, prendre le premier
            $actors = $this->actorRepository->findBy(['firstName' => $firstName, 'lastName' => $lastName], ['id' => 'ASC'], 1);
            $actor = $actors ? $actors[0] : null;
        }
        
        if (!$actor) {
            $actor = new Actor();
            $actor->setFirstName($firstName)->setLastName($lastName);
            $this->entityManager->persist($actor);
        }
        return $actor;
    }

    private function findOrCreateTag(string $name): Tag
    {
        try {
            $tag = $this->tagRepository->findOneByName($name);
        } catch (\Exception $e) {
            // Si plusieurs résultats, prendre le premier
            $tags = $this->tagRepository->findBy(['name' => $name], ['id' => 'ASC'], 1);
            $tag = $tags ? $tags[0] : null;
        }
        
        if (!$tag) {
            $colors = ['#dc3545', '#198754', '#0d6efd', '#fd7e14', '#6f42c1', '#20c997', '#ffc107'];
            $tag = new Tag();
            $tag->setName($name)->setColor($colors[array_rand($colors)]);
            $this->entityManager->persist($tag);
        }
        return $tag;
    }
}