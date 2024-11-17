<?php

declare(strict_types=1);

namespace App\Property\Transport\Controller\Ajax\User;

use App\Media\Application\Service\FileUploader;
use App\Platform\Transport\Controller\Ajax\AjaxController;
use App\Property\Domain\Entity\Property;
use App\Property\Transport\Controller\AbstractPhotoController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Class PhotoController
 *
 * @package App\Controller\Ajax\User
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class PhotoController extends AbstractPhotoController implements AjaxController
{
    #[Route(
        path: '/user/photo/{id}/upload',
        name: 'user_photo_upload',
        requirements: ['id' => Requirement::POSITIVE_INT],
        methods: ['POST']
    )]
    #[IsGranted('PROPERTY_EDIT', subject: 'property', message: 'You cannot change this property.')]
    public function upload(Property $property, Request $request, FileUploader $fileUploader): JsonResponse
    {
        return $this->uploadPhoto($property, $request, $fileUploader);
    }

    /**
     * Sort photos.
     */
    #[Route(
        path: '/user/photo/{id}/sort',
        name: 'user_photo_sort',
        requirements: ['id' => Requirement::POSITIVE_INT],
        methods: ['POST'])
    ]
    #[IsGranted('PROPERTY_EDIT', subject: 'property', message: 'You cannot change this property.')]
    public function sort(Request $request, Property $property): JsonResponse
    {
        return $this->sortPhotos($request, $property);
    }
}
