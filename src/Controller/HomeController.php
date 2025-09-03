<?php

namespace App\Controller;

use App\Repository\MovieRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[Route('/page/{page<\\d+>}', name: 'app_home_paginated')]
    public function index(Request $request, MovieRepository $movieRepository, PaginatorInterface $paginator, int $page = 1): Response
    {
        $query = $movieRepository->findAllOrderedByAddedAt();

        $pagination = $paginator->paginate(
            $query,
            $page,
            12
        );

        return $this->render('home/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}