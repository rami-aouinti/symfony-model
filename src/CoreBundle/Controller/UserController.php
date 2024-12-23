<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller;

use App\CoreBundle\Repository\Node\IllustrationRepository;
use App\CoreBundle\Repository\Node\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Julio Montoya <gugli100@gmail.com>
 */
#[Route('/user')]
class UserController extends AbstractController
{
    /**
     * Public profile.
     */
    #[Route(path: '/{username}', name: 'chamilo_core_user_profile', methods: ['GET'])]
    public function profile(string $username, UserRepository $userRepository, IllustrationRepository $illustrationRepository): Response
    {
        $user = $userRepository->findByUsername($username);

        if (!\is_object($user) || !$user instanceof UserInterface) {
            throw $this->createAccessDeniedException('This user does not have access to this section');
        }

        $url = $illustrationRepository->getIllustrationUrl($user);

        return $this->render('@ChamiloCore/User/profile.html.twig', [
            'user' => $user,
            'illustration_url' => $url,
        ]);
    }
}
