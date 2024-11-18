<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Controller\OAuth2;

use App\CoreBundle\ServiceHelper\AuthenticationConfigHelper;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractProviderController extends AbstractController
{
    protected function getStartResponse(
        string $providerName,
        ClientRegistry $clientRegistry,
        AuthenticationConfigHelper $authenticationConfigHelper,
    ): Response {
        if (!$authenticationConfigHelper->isEnabled($providerName)) {
            throw $this->createAccessDeniedException();
        }

        return $clientRegistry->getClient($providerName)->redirect();
    }
}
