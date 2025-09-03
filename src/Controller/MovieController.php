<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/movie')]
class MovieController extends AbstractController
{
    #[Route('/{slug}', name: 'app_movie_show', methods: ['GET'])]
    public function show(string $slug, MovieRepository $movieRepository): Response
    {
        $movie = $movieRepository->findOneBySlug($slug);
        
        if (!$movie) {
            throw $this->createNotFoundException('Film non trouvé');
        }

        return $this->render('movie/show.html.twig', [
            'movie' => $movie,
        ]);
    }
}