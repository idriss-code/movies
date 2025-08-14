<?php

namespace App\Controller;

use App\Repository\MovieRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(Request $request, MovieRepository $movieRepository, PaginatorInterface $paginator): Response
    {
        $searchQuery = $request->query->get('q', '');
        $results = [];

        if ($searchQuery) {
            $query = $movieRepository->searchMovies($searchQuery);

            $results = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                12
            );
        }

        return $this->render('search/index.html.twig', [
            'search_query' => $searchQuery,
            'results' => $results,
        ]);
    }
}