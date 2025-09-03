<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clean-duplicates',
    description: 'Clean duplicate studios, directors, and actors'
)]
class CleanDuplicatesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Nettoyage des doublons');

        // Nettoyer les studios en double
        $io->section('Nettoyage des studios...');
        $studiosCleaned = $this->cleanStudioDuplicates($io);
        
        // Nettoyer les réalisateurs en double
        $io->section('Nettoyage des réalisateurs...');
        $directorsCleaned = $this->cleanDirectorDuplicates($io);
        
        // Nettoyer les acteurs en double
        $io->section('Nettoyage des acteurs...');
        $actorsCleaned = $this->cleanActorDuplicates($io);

        $this->entityManager->flush();

        $io->success(sprintf(
            'Nettoyage terminé: %d studios, %d réalisateurs, %d acteurs nettoyés',
            $studiosCleaned,
            $directorsCleaned,
            $actorsCleaned
        ));

        return Command::SUCCESS;
    }

    private function cleanStudioDuplicates(SymfonyStyle $io): int
    {
        $sql = "SELECT name, COUNT(*) as count FROM studio GROUP BY name HAVING COUNT(*) > 1";
        $duplicates = $this->entityManager->getConnection()->fetchAllAssociative($sql);
        
        $cleaned = 0;
        
        foreach ($duplicates as $duplicate) {
            $name = $duplicate['name'];
            $io->text("Nettoyage studio: {$name}");
            
            // Récupérer tous les studios avec ce nom
            $studios = $this->entityManager->createQuery(
                'SELECT s FROM App\Entity\Studio s WHERE s.name = :name ORDER BY s.id ASC'
            )->setParameter('name', $name)->getResult();
            
            if (count($studios) > 1) {
                $keepStudio = $studios[0]; // Garder le premier
                
                // Transférer tous les films vers le premier studio
                for ($i = 1; $i < count($studios); $i++) {
                    $duplicateStudio = $studios[$i];
                    
                    $this->entityManager->createQuery(
                        'UPDATE App\Entity\Movie m SET m.studio = :keep WHERE m.studio = :duplicate'
                    )
                    ->setParameter('keep', $keepStudio)
                    ->setParameter('duplicate', $duplicateStudio)
                    ->execute();
                    
                    $this->entityManager->remove($duplicateStudio);
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }

    private function cleanDirectorDuplicates(SymfonyStyle $io): int
    {
        $sql = "SELECT first_name, last_name, COUNT(*) as count FROM director GROUP BY first_name, last_name HAVING COUNT(*) > 1";
        $duplicates = $this->entityManager->getConnection()->fetchAllAssociative($sql);
        
        $cleaned = 0;
        
        foreach ($duplicates as $duplicate) {
            $firstName = $duplicate['first_name'];
            $lastName = $duplicate['last_name'];
            $io->text("Nettoyage réalisateur: {$firstName} {$lastName}");
            
            // Récupérer tous les réalisateurs avec ce nom
            $directors = $this->entityManager->createQuery(
                'SELECT d FROM App\Entity\Director d WHERE d.firstName = :firstName AND d.lastName = :lastName ORDER BY d.id ASC'
            )->setParameters(['firstName' => $firstName, 'lastName' => $lastName])->getResult();
            
            if (count($directors) > 1) {
                $keepDirector = $directors[0]; // Garder le premier
                
                // Transférer tous les films vers le premier réalisateur
                for ($i = 1; $i < count($directors); $i++) {
                    $duplicateDirector = $directors[$i];
                    
                    $this->entityManager->createQuery(
                        'UPDATE App\Entity\Movie m SET m.director = :keep WHERE m.director = :duplicate'
                    )
                    ->setParameter('keep', $keepDirector)
                    ->setParameter('duplicate', $duplicateDirector)
                    ->execute();
                    
                    $this->entityManager->remove($duplicateDirector);
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }

    private function cleanActorDuplicates(SymfonyStyle $io): int
    {
        $sql = "SELECT first_name, last_name, COUNT(*) as count FROM actor GROUP BY first_name, last_name HAVING COUNT(*) > 1";
        $duplicates = $this->entityManager->getConnection()->fetchAllAssociative($sql);
        
        $cleaned = 0;
        
        foreach ($duplicates as $duplicate) {
            $firstName = $duplicate['first_name'];
            $lastName = $duplicate['last_name'];
            $io->text("Nettoyage acteur: {$firstName} {$lastName}");
            
            // Récupérer tous les acteurs avec ce nom
            $actors = $this->entityManager->createQuery(
                'SELECT a FROM App\Entity\Actor a WHERE a.firstName = :firstName AND a.lastName = :lastName ORDER BY a.id ASC'
            )->setParameters(['firstName' => $firstName, 'lastName' => $lastName])->getResult();
            
            if (count($actors) > 1) {
                $keepActor = $actors[0]; // Garder le premier
                
                // Transférer toutes les relations vers le premier acteur
                for ($i = 1; $i < count($actors); $i++) {
                    $duplicateActor = $actors[$i];
                    
                    // Récupérer tous les films de l'acteur en double
                    $movies = $duplicateActor->getMovies();
                    foreach ($movies as $movie) {
                        $movie->removeActor($duplicateActor);
                        if (!$movie->getActors()->contains($keepActor)) {
                            $movie->addActor($keepActor);
                        }
                    }
                    
                    $this->entityManager->remove($duplicateActor);
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }
}