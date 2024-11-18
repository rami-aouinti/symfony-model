<?php

declare(strict_types=1);

namespace App\User\Transport\Controller\Auth;

use App\Admin\Application\Service\UserService;
use App\Configuration\Infrastructure\Repository\SettingsRepository;
use App\Platform\Transport\Controller\BaseController;
use App\User\Application\Security\RegistrationFormAuthenticator;
use App\User\Domain\Entity\Profile;
use App\User\Domain\Entity\User;
use App\User\Transport\Form\Type\RegistrationFormType;
use App\User\Transport\Message\SendEmailConfirmationLink;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

/**
 * @package App\Controller\Auth
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class RegisterController extends BaseController implements AuthController
{
    private readonly array $settings;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly RegistrationFormAuthenticator $authenticator,
        private readonly Security $security,
        private readonly UserAuthenticatorInterface $userAuthenticator,
        private readonly UserService $service,
        ManagerRegistry $doctrine,
        RequestStack $requestStack,
        SettingsRepository $settingsRepository
    ) {
        parent::__construct($settingsRepository, $doctrine);
        $this->settings = $this->site($requestStack->getCurrentRequest());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    #[Route('/register', name: 'register')]
    public function register(Request $request): ?Response
    {
        if ($this->security->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('user_property');
        } elseif ($this->settings['anyone_can_register'] !== '1') {
            $this->addFlash('danger', 'message.registration_suspended');

            return $this->redirectToRoute('property');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setProfile(new Profile());
            $this->service->create($user);
            $this->messageBus->dispatch(new SendEmailConfirmationLink($user));

            return $this->authenticate($user, $request);
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form,
            'site' => $this->settings,
        ]);
    }

    private function authenticate(User $user, Request $request): ?Response
    {
        return $this->userAuthenticator->authenticateUser(
            $user,
            $this->authenticator,
            $request
        );
    }
}
