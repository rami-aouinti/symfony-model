<?php

declare(strict_types=1);

namespace App\User\Transport\Controller;

use App\User\Domain\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * @package App\Controller
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class SecurityController extends AbstractController
{
    use TargetPathTrait;

    /**
     * @param User|null $user
     */
    #[Route('/login', name: 'security_login')]
    public function login(
        #[CurrentUser]
        ?User $user,
        Request $request,
        AuthenticationUtils $helper,
    ): Response {
        if ($user) {
            return $this->redirectToRoute('blog_index');
        }

        $this->saveTargetPath(
            $request->getSession(),
            'main',
            $this->generateUrl('admin_index')
        );

        return $this->render('security/login.html.twig', [
            'last_username' => $helper->getLastUsername(),
            'error' => $helper->getLastAuthenticationError(),
        ]);
    }
}
