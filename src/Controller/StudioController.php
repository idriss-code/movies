<?php

namespace App\Controller;

use App\Entity\Studio;
use App\Repository\MovieRepository;
use App\Repository\StudioRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/studio')]
class StudioController extends AbstractController
{
    #[Route('s', name: 'app_studio_index', methods: ['GET'])]
    public function index(Request $request, StudioRepository $studioRepository, PaginatorInterface $paginator): Response
    {
        $query = $studioRepository->findAllWithMovieCount();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('studio/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{id}', name: 'app_studio_show', methods: ['GET'])]
    public function show(Request $request, Studio $studio, MovieRepository $movieRepository, PaginatorInterface $paginator): Response
    {
        $query = $movieRepository->findByStudioOrderedByAddedAt($studio);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('studio/show.html.twig', [
            'studio' => $studio,
            'pagination' => $pagination,
        ]);
    }
}