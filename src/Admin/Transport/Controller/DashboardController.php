<?php

declare(strict_types=1);

namespace App\Admin\Transport\Controller;

use App\Admin\Application\Service\DashboardService;
use App\Platform\Transport\Controller\BaseController;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class DashboardController
 *
 * @package App\Admin\Transport\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class DashboardController extends BaseController
{
    /**
     * @throws InvalidArgumentException
     */
    #[Route(path: '/admin', name: 'admin_dashboard')]
    public function index(Request $request, DashboardService $service): Response
    {
        $properties = $service->countProperties();


        $cities = $service->countCities();

        $dealTypes = $service->countDealTypes();

        $categories = $service->countCategories();

        $pages = $service->countPages();

        $users = $service->countUsers();

        return $this->render('admin/dashboard/index.html.twig', [
            'site' => $this->site($request),
            'number_of_properties' => $properties,
            'number_of_cities' => $cities,
            'number_of_deal_types' => $dealTypes,
            'number_of_categories' => $categories,
            'number_of_pages' => $pages,
            'number_of_users' => $users,
        ]);
    }
}
