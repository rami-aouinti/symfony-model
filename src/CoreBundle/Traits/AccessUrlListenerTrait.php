<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Traits;

use App\Access\Domain\Entity\AccessUrl;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
trait AccessUrlListenerTrait
{
    protected ?AccessUrl $accessUrl = null;

    public function getAccessUrl(EntityManagerInterface $em, RequestStack $request): ?AccessUrl
    {
        if ($this->accessUrl === null) {
            $request = $request->getCurrentRequest();
            if ($request === null) {
                return null;
            }

            $sessionRequest = $request->getSession();
            $id = (int)$sessionRequest->get('access_url_id');
            if ($id !== 0) {
                /** @var AccessUrl $url */
                $url = $em->getRepository(AccessUrl::class)->find($id);

                if ($url !== null) {
                    $this->accessUrl = $url;

                    return $url;
                }
            }

            return null;
        }

        return $this->accessUrl;
    }
}
