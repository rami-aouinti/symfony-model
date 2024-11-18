<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\ServiceHelper;

use App\Access\Domain\Entity\AccessUrl;
use App\CoreBundle\Repository\Node\AccessUrlRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class AccessUrlHelper
{
    public function __construct(
        private readonly AccessUrlRepository $accessUrlRepository,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function isMultiple(): bool
    {
        static $accessUrlEnabled;

        if (!isset($accessUrlEnabled)) {
            $accessUrlEnabled = $this->accessUrlRepository->count([]) > 1;
        }

        return $accessUrlEnabled;
    }

    public function getFirstAccessUrl(): ?AccessUrl
    {
        $urlId = $this->accessUrlRepository->getFirstId();

        return $this->accessUrlRepository->find($urlId) ?: null;
    }

    public function getCurrent(): ?AccessUrl
    {
        static $accessUrl;

        if (!empty($accessUrl)) {
            return $accessUrl;
        }

        if ('cli' === PHP_SAPI) {
            return $this->getFirstAccessUrl();
        }

        $accessUrl = $this->getFirstAccessUrl();

        if ($this->isMultiple()) {
            $request = $this->requestStack->getMainRequest();

            if ($request === null) {
                return $accessUrl;
            }

            $url = $request->getSchemeAndHttpHost() . '/';

            /** @var AccessUrl $accessUrl */
            $accessUrl = $this->accessUrlRepository->findOneBy([
                'url' => $url,
            ]);
        }

        return $accessUrl;
    }
}
