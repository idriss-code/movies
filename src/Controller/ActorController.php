<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Repository\ActorRepository;
use App\Repository\MovieRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/actor')]
class ActorController extends AbstractController
{
    #[Route('s', name: 'app_actor_index', methods: ['GET'])]
    #[Route('s/page/{page<\\d+>}', name: 'app_actor_index_paginated', methods: ['GET'])]
    public function index(Request $request, ActorRepository $actorRepository, PaginatorInterface $paginator, int $page = 1): Response
    {
        $query = $actorRepository->findAllWithMovieCount();

        $pagination = $paginator->paginate(
            $query,
            $page,
            20
        );

        return $this->render('actor/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{id}', name: 'app_actor_show', methods: ['GET'])]
    #[Route('/{id}/page/{page<\\d+>}', name: 'app_actor_show_paginated', methods: ['GET'])]
    public function show(Request $request, Actor $actor, MovieRepository $movieRepository, PaginatorInterface $paginator, int $page = 1): Response
    {
        $query = $movieRepository->findByActorOrderedByAddedAt($actor);

        $pagination = $paginator->paginate(
            $query,
            $page,
            12
        );

        return $this->render('actor/show.html.twig', [
            'actor' => $actor,
            'pagination' => $pagination,
        ]);
    }
}