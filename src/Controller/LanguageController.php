<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LanguageController extends AbstractController
{
    #[Route('/language/{locale}', name: 'app_language_switch', requirements: ['locale' => 'fr|en|es'])]
    public function switch(Request $request, string $locale): Response
    {
        // Store the locale in session
        $request->getSession()->set('_locale', $locale);
        
        // Redirect to the referer or home page
        $referer = $request->headers->get('referer');
        $redirectUrl = $referer ?: $this->generateUrl('app_home');
        
        return $this->redirect($redirectUrl);
    }
}