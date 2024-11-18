<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\EventListener;

use App\CoreBundle\ServiceHelper\CidReqHelper;
use App\CoreBundle\ServiceHelper\UserHelper;
use App\Track\Domain\Entity\TrackECourseAccess;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * In and outs of a course
 * This listener is always called when user enters the course home.
 */
class CourseAccessListener
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CidReqHelper $cidReqHelper,
        private readonly UserHelper $userHelper
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || $event->getRequest()->attributes->get('access_checked')) {
            // If it's not the main request or we've already handled access in this request, do nothing
            return;
        }

        $courseId = (int)$this->cidReqHelper->getCourseId();
        $sessionId = (int)$this->cidReqHelper->getSessionId();

        if ($courseId > 0) {
            $user = $this->userHelper->getCurrent();
            if ($user) {
                $ip = $event->getRequest()->getClientIp();
                $accessRepository = $this->em->getRepository(TrackECourseAccess::class);
                $access = $accessRepository->findExistingAccess($user, $courseId, $sessionId);

                if ($access) {
                    $accessRepository->updateAccess($access);
                } else {
                    $accessRepository->recordAccess($user, $courseId, $sessionId, $ip);
                }

                // Set a flag on the request to indicate that access has been checked
                $event->getRequest()->attributes->set('access_checked', true);
            }
        }
    }
}
