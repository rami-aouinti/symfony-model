<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Security\Authenticator\OAuth2;

use App\CoreBundle\Entity\User\User;
use League\OAuth2\Client\Token\AccessToken;
use Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner;
use Symfony\Component\HttpFoundation\Request;

class KeycloakAuthenticator extends AbstractAuthenticator
{
    protected string $providerName = 'keycloak';

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'chamilo.oauth2_keycloak_check';
    }

    protected function userLoader(AccessToken $accessToken): User
    {
        /** @var KeycloakResourceOwner $resourceOwner */
        $resourceOwner = $this->client->fetchUserFromToken($accessToken);

        $user = $this->userRepository->findOneBy([
            'username' => $resourceOwner->getUsername(),
        ])
            ?:
            $this->userRepository->findOneBy([
                'username' => $resourceOwner->getId(),
            ]);

        if (!$user) {
            $user = (new User())
                ->setCreatorId($this->userRepository->getRootUser()->getId())
            ;
        }

        $username = $resourceOwner->getUsername() ?: $resourceOwner->getId();

        $user
            ->setFirstname($resourceOwner->getFirstName())
            ->setLastname($resourceOwner->getLastName())
            ->setEmail($resourceOwner->getEmail())
            ->setUsername($username)
            ->setPlainPassword('keycloak')
            ->setStatus(STUDENT)
            ->setAuthSource('keycloak')
            ->setRoleFromStatus(STUDENT)
        ;

        $this->userRepository->updateUser($user);

        $url = $this->urlHelper->getCurrent();
        $url->addUser($user);

        $this->entityManager->flush();

        return $user;
    }
}
