<?php

declare(strict_types=1);

namespace App\Property\Transport\Controller;

use App\Place\Infrastructure\Repository\CityRepository;
use App\Property\Infrastructure\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class SitemapController
 *
 * @package App\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class SitemapController extends AbstractController
{
    private const array DEFAULTS = ['_format' => 'xml'];

    #[Route(path: '/sitemap.xml', name: 'sitemap', defaults: self::DEFAULTS)]
    public function siteMap(): Response
    {
        return $this->render('sitemap/sitemap.xml.twig', []);
    }

    #[Route(path: '/sitemap/cities.xml', name: 'cities_sitemap', defaults: self::DEFAULTS)]
    public function cities(CityRepository $cityRepository): Response
    {
        $cities = $cityRepository->findAll();

        return $this->render('sitemap/cities.xml.twig', [
            'cities' => $cities,
        ]);
    }

    #[Route(path: '/sitemap/properties.xml', name: 'properties_sitemap', defaults: self::DEFAULTS)]
    public function properties(PropertyRepository $propertyRepository): Response
    {
        $properties = $propertyRepository->findAllPublished();

        return $this->render('sitemap/properties.xml.twig', [
            'properties' => $properties,
        ]);
    }
}
