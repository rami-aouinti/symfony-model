<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Controller\OAuth2;

use App\CoreBundle\ServiceHelper\AuthenticationConfigHelper;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GenericProviderController extends AbstractProviderController
{
    #[Route('/connect/generic', name: 'chamilo.oauth2_generic_start')]
    public function connect(
        ClientRegistry $clientRegistry,
        AuthenticationConfigHelper $authenticationConfigHelper,
    ): Response {
        return $this->getStartResponse('generic', $clientRegistry, $authenticationConfigHelper);
    }

    #[Route('/connect/generic/check', name: 'chamilo.oauth2_generic_check')]
    public function connectCheck(): void
    {
    }
}