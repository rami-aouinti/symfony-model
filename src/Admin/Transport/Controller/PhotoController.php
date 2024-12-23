<?php

declare(strict_types=1);

namespace App\Admin\Transport\Controller;

use App\Media\Application\Service\FileUploader;
use App\Media\Domain\Entity\Photo;
use App\Platform\Transport\Controller\BaseController;
use App\Property\Domain\Entity\Property;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @package App\Admin\Transport\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class PhotoController extends BaseController
{
    #[Route(
        path: '/admin/photo/{id}/edit',
        name: 'admin_photo_edit',
        requirements: [
            'id' => Requirement::POSITIVE_INT,
        ]
    )]
    public function edit(Request $request, Property $property): Response
    {
        $photos = $property->getPhotos();

        return $this->render('admin/photo/edit.html.twig', [
            'site' => $this->site($request),
            'photos' => $photos,
            'property_id' => $property->getId(),
        ]);
    }

    /**
     * Deletes a Photo entity.
     */
    #[Route(
        path: '/property/{property_id}/photo/{id}/delete',
        name: 'admin_photo_delete',
        requirements: [
            'property_id' => Requirement::POSITIVE_INT,
            'id' => Requirement::POSITIVE_INT,
        ],
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Photo $photo, FileUploader $fileUploader): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->getPayload()->get('token'))) {
            return $this->redirectToRoute(
                'admin_photo_edit',
                [
                    'id' => $request->attributes->get('property_id'),
                ]
            );
        }

        // Delete from db
        $em = $this->doctrine->getManager();
        $em->remove($photo);
        $em->flush();

        // Delete file from folder
        $fileUploader->remove($photo->getPhoto());

        $this->addFlash('success', 'message.deleted');

        return $this->redirectToRoute(
            'admin_photo_edit',
            [
                'id' => $request->attributes->get('property_id'),
            ]
        );
    }
}
