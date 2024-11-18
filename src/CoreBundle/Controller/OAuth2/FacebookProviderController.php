<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Controller\OAuth2;

use App\CoreBundle\ServiceHelper\AuthenticationConfigHelper;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FacebookProviderController extends AbstractProviderController
{
    #[Route('/connect/facebook', name: 'chamilo.oauth2_facebook_start')]
    public function connect(
        ClientRegistry $clientRegistry,
        AuthenticationConfigHelper $authenticationConfigHelper,
    ): Response {
        return $this->getStartResponse('facebook', $clientRegistry, $authenticationConfigHelper);
    }

    #[Route('/connect/facebook/check', name: 'chamilo.oauth2_facebook_check')]
    public function connectCheck(): void
    {
    }
}
