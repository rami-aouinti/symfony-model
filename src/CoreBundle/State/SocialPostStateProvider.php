<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\CoreBundle\Settings\SettingsManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SocialPostStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private readonly ProviderInterface $itemProvider,
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private readonly ProviderInterface $collectionProvider,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($this->settingsManager->getSetting('social.allow_social_tool') !== 'true') {
            throw new AccessDeniedHttpException();
        }

        if ($operation instanceof CollectionOperationInterface) {
            return $this->collectionProvider->provide($operation, $uriVariables, $context);
        }

        return $this->itemProvider->provide($operation, $uriVariables, $context);
    }
}
