<?php

declare(strict_types=1);

namespace App\User\Transport\Controller;

use App\User\Domain\Entity\User;
use App\User\Transport\Form\ChangePasswordType;
use App\User\Transport\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @package App\Controller
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[Route('/profile'), IsGranted(User::ROLE_USER)]
final class UserController extends AbstractController
{
    #[Route('/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(
        #[CurrentUser]
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'user.updated_successfully');

            return $this->redirectToRoute('user_edit', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/change-password', name: 'user_change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        #[CurrentUser]
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
    ): Response {
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $security->logout(validateCsrfToken: false) ?? $this->redirectToRoute('homepage');
        }

        return $this->render('user/change_password.html.twig', [
            'form' => $form,
        ]);
    }
}
