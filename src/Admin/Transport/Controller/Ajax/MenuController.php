<?php

declare(strict_types=1);

namespace App\Admin\Transport\Controller\Ajax;

use App\Platform\Infrastructure\Repository\MenuRepository;
use App\Platform\Transport\Controller\Ajax\AjaxController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @package App\Controller\Ajax\Admin
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class MenuController extends AbstractController implements AjaxController
{
    /**
     * Sort menu items.
     */
    #[Route(path: '/admin/menu/sort', name: 'admin_menu_sort', methods: ['POST'])]
    public function sort(Request $request, MenuRepository $repository): JsonResponse
    {
        $items = $request->getPayload()->all('items');
        $repository->reorderItems($items);

        return new JsonResponse([
            'status' => 'ok',
        ]);
    }
}
