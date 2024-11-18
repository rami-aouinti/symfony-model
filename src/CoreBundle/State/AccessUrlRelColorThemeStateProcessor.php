<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Access\Domain\Entity\AccessUrlRelColorTheme;
use App\CoreBundle\ServiceHelper\AccessUrlHelper;
use Doctrine\ORM\EntityManagerInterface;

use function assert;

/**
 * @package App\CoreBundle\State
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final readonly class AccessUrlRelColorThemeStateProcessor implements ProcessorInterface
{
    public function __construct(
        private AccessUrlHelper $accessUrlHelper,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param           $data
     * @param Operation $operation
     * @param array     $uriVariables
     * @param array     $context
     *
     * @return AccessUrlRelColorTheme
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): AccessUrlRelColorTheme
    {
        assert($data instanceof AccessUrlRelColorTheme);

        $accessUrl = $this->accessUrlHelper->getCurrent();
        $accessUrl->getActiveColorTheme()?->setActive(false);

        $accessUrlRelColorTheme = $accessUrl->getColorThemeByTheme($data->getColorTheme());

        if ($accessUrlRelColorTheme) {
            $accessUrlRelColorTheme->setActive(true);
        } else {
            $data->setActive(true);

            $accessUrl->addColorTheme($data);

            $accessUrlRelColorTheme = $data;
        }

        $this->entityManager->flush();

        return $accessUrlRelColorTheme;
    }
}
