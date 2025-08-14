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
    public function index(Request $request, MovieRepository $movieRepository, PaginatorInterface $paginator): Response
    {
        $query = $movieRepository->findAllOrderedByAddedAt();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('home/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}