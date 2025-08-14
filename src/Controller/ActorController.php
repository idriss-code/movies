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
    public function index(Request $request, ActorRepository $actorRepository, PaginatorInterface $paginator): Response
    {
        $query = $actorRepository->findAllWithMovieCount();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('actor/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{id}', name: 'app_actor_show', methods: ['GET'])]
    public function show(Request $request, Actor $actor, MovieRepository $movieRepository, PaginatorInterface $paginator): Response
    {
        $query = $movieRepository->findByActorOrderedByAddedAt($actor);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('actor/show.html.twig', [
            'actor' => $actor,
            'pagination' => $pagination,
        ]);
    }
}