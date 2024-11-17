<?php

declare(strict_types=1);

namespace App\User\Transport\Controller\User;

use App\Platform\Transport\Controller\BaseController;
use App\User\Domain\Entity\User;
use App\User\Transport\Form\Type\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class ProfileController
 *
 * @package App\Controller\User
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class ProfileController extends BaseController
{
    #[Route('/user/profile', name: 'user_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $profile = $user->getProfile();
        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($profile);
            $entityManager->flush();
            $this->addFlash('success', 'message.updated');
        }

        return $this->render('user/profile/profile.html.twig', [
            'site' => $this->site($request),
            'form' => $form,
        ]);
    }
}
