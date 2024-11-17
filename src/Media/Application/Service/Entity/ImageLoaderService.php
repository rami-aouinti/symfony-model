<?php

declare(strict_types=1);

/*
 * This file is part of the bjoern-hempel/php-calendar-api project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Media\Application\Service\Entity;

use App\Media\Domain\Entity\Image;
use App\Media\Infrastructure\Repository\ImageRepository;
use App\Platform\Application\Service\Entity\Base\BaseLoaderService;
use App\User\Application\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-02-15)
 * @package App\Command
 */
class ImageLoaderService extends BaseLoaderService
{
    public function __construct(
        protected KernelInterface $appKernel,
        protected EntityManagerInterface $manager,
        protected SecurityService $securityService
    ) {
    }

    /**
     * Loads all images by permissions.
     *
     * @return Image[]
     * @throws Exception
     */
    public function loadImages(): array
    {
        if ($this->securityService->isGrantedByAnAdmin()) {
            return $this->getImageRepository()->findAll();
        }

        return $this->getImageRepository()->findBy([
            'user' => $this->securityService->getUser(),
        ]);
    }

    /**
     * Returns the ImageRepository.
     *
     * @throws Exception
     */
    protected function getImageRepository(): ImageRepository
    {
        $repository = $this->manager->getRepository(Image::class);

        if (!$repository instanceof ImageRepository) {
            throw new Exception('Error while getting ImageRepository.');
        }

        return $repository;
    }
}
