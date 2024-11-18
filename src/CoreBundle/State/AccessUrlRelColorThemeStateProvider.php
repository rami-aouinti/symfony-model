<?php

declare(strict_types=1);

namespace App\CoreBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Access\Domain\Entity\AccessUrlRelColorTheme;
use App\CoreBundle\ServiceHelper\AccessUrlHelper;

/**
 * @template-implements ProviderInterface<AccessUrlRelColorTheme>
 */
readonly class AccessUrlRelColorThemeStateProvider implements ProviderInterface
{
    public function __construct(
        private AccessUrlHelper $accessUrlHelper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $colorThemes = $this->accessUrlHelper->getCurrent()->getColorThemes();

        if ($colorThemes->count() == 0) {
            $colorThemes = $this->accessUrlHelper->getFirstAccessUrl()->getColorThemes();
        }

        return $colorThemes;
    }
}
