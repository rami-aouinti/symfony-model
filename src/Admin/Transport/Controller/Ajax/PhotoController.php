<?php

declare(strict_types=1);

namespace App\Admin\Transport\Controller\Ajax;

use App\Media\Application\Service\FileUploader;
use App\Platform\Transport\Controller\Ajax\AjaxController;
use App\Property\Domain\Entity\Property;
use App\Property\Transport\Controller\AbstractPhotoController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

/**
 * @package App\Controller\Ajax\Admin
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class PhotoController extends AbstractPhotoController implements AjaxController
{
    #[Route(
        path: '/admin/photo/{id}/upload',
        name: 'admin_photo_upload',
        requirements: [
            'id' => Requirement::POSITIVE_INT,
        ],
        methods: ['POST']
    )]
    public function upload(Property $property, Request $request, FileUploader $fileUploader): JsonResponse
    {
        return $this->uploadPhoto($property, $request, $fileUploader);
    }

    /**
     * Sort photos.
     */
    #[Route(
        path: '/admin/photo/{id}/sort',
        name: 'admin_photo_sort',
        requirements: [
            'id' => Requirement::POSITIVE_INT,
        ],
        methods: ['POST']
    )]
    public function sort(Request $request, Property $property): JsonResponse
    {
        return $this->sortPhotos($request, $property);
    }
}
