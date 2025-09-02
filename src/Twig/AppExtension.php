<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\Extension\GlobalsInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private TranslatorInterface $translator,
        private ParameterBagInterface $parameterBag
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'adult_warning_enabled' => $this->parameterBag->get('app.adult_warning_enabled'),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('localized_date', [$this, 'localizedDate']),
            new TwigFilter('movie_poster', [$this, 'moviePosterPath']),
        ];
    }

    public function localizedDate(\DateTimeInterface $date, string $format = 'medium'): string
    {
        $locale = $this->translator->getLocale();
        
        $formats = [
            'fr' => [
                'short' => 'd/m/Y',
                'medium' => 'd/m/Y à H:i',
                'long' => 'l j F Y à H:i'
            ],
            'en' => [
                'short' => 'm/d/Y',
                'medium' => 'm/d/Y at H:i',
                'long' => 'l F jS, Y at H:i'
            ],
            'es' => [
                'short' => 'd/m/Y',
                'medium' => 'd/m/Y a las H:i',
                'long' => 'l j \d\e F \d\e Y a las H:i'
            ]
        ];

        $formatString = $formats[$locale][$format] ?? $formats['en'][$format];
        
        return $date->format($formatString);
    }

    public function moviePosterPath(?string $filename): string
    {
        if (!$filename) {
            return '/images/films/default-poster.jpg';
        }
        
        return '/images/films/' . $filename;
    }
}