<?php

declare(strict_types=1);

namespace App\CoreBundle\Controller\Api;

use App\Blog\Domain\Entity\SocialPost;
use App\Blog\Domain\Entity\SocialPostAttachment;
use App\CoreBundle\Repository\Node\SocialPostAttachmentRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

class SocialPostAttachmentsController extends BaseResourceFileAction
{
    public function __invoke(SocialPost $socialPost, EntityManager $em, Security $security, SocialPostAttachmentRepository $attachmentRepo): JsonResponse
    {
        $attachments = $em->getRepository(SocialPostAttachment::class)->findBy([
            'socialPost' => $socialPost->getId(),
        ]);

        $attachmentsInfo = [];
        if ($attachments) {
            foreach ($attachments as $attachment) {
                $attachmentsInfo[] = [
                    'id' => $attachment->getId(),
                    'filename' => $attachment->getFilename(),
                    'path' => $attachmentRepo->getResourceFileUrl($attachment),
                    'size' => $attachment->getSize(),
                ];
            }
        }

        return new JsonResponse($attachmentsInfo, JsonResponse::HTTP_OK);
    }
}
