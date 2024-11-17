<?php

declare(strict_types=1);

namespace App\User\Transport\Controller\Ajax\User\Security;

use App\Platform\Transport\Controller\Ajax\AjaxController;
use App\User\Application\Service\User\PasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class PasswordController
 *
 * @package App\Controller\Ajax\User\Security
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class PasswordController extends AbstractController implements AjaxController
{
    #[Route(path: '/user/password', name: 'user_password', methods: ['POST'])]
    public function update(Request $request, PasswordService $service): JsonResponse
    {
        try {
            $service->update($request);

            return new JsonResponse([]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
