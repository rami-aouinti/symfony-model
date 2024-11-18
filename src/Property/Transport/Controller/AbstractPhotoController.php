<?php

declare(strict_types=1);

namespace App\Property\Transport\Controller;

use App\Media\Application\Service\FileUploader;
use App\Media\Domain\Entity\Photo;
use App\Media\Infrastructure\Repository\PhotoRepository;
use App\Property\Domain\Entity\Property;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * @package App\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
abstract class AbstractPhotoController extends AbstractController
{
    public function __construct(
        protected ManagerRegistry $doctrine
    ) {
    }

    protected function uploadPhoto(Property $property, Request $request, FileUploader $fileUploader): JsonResponse
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $violations = $fileUploader->validate($uploadedFile);

        if ($violations->count() > 0) {
            /** @var ConstraintViolation $violation */
            $violation = $violations[0];
            $this->addFlash('danger', $violation->getMessage());

            return new JsonResponse([
                'status' => 'fail',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $fileName = $fileUploader->upload($uploadedFile);

        $photo = new Photo();
        $photo->setProperty($property)
            ->setSortOrder(0)
            ->setPhoto($fileName);

        $em = $this->doctrine->getManager();
        $em->persist($photo);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
        ]);
    }

    protected function sortPhotos(Request $request, Property $property): JsonResponse
    {
        $ids = $request->getPayload()->all('ids');
        /** @var PhotoRepository $repository */
        $repository = $this->doctrine->getRepository(Photo::class);
        $repository->reorderPhotos($property, $ids);

        return new JsonResponse([
            'status' => 'ok',
        ]);
    }
}
