<?php

namespace App\Controller;

use App\Entity\Director;
use App\Repository\DirectorRepository;
use App\Repository\MovieRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/director')]
class DirectorController extends AbstractController
{
    #[Route('s', name: 'app_director_index', methods: ['GET'])]
    #[Route('s/page/{page<\\d+>}', name: 'app_director_index_paginated', methods: ['GET'])]
    public function index(Request $request, DirectorRepository $directorRepository, PaginatorInterface $paginator, int $page = 1): Response
    {
        $query = $directorRepository->findAllWithMovieCount();

        $pagination = $paginator->paginate(
            $query,
            $page,
            20
        );

        return $this->render('director/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{id}', name: 'app_director_show', methods: ['GET'])]
    #[Route('/{id}/page/{page<\\d+>}', name: 'app_director_show_paginated', methods: ['GET'])]
    public function show(Request $request, Director $director, MovieRepository $movieRepository, PaginatorInterface $paginator, int $page = 1): Response
    {
        $query = $movieRepository->findByDirectorOrderedByAddedAt($director);

        $pagination = $paginator->paginate(
            $query,
            $page,
            12
        );

        return $this->render('director/show.html.twig', [
            'director' => $director,
            'pagination' => $pagination,
        ]);
    }
}