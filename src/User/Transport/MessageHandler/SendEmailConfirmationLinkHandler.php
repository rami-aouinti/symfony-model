<?php

declare(strict_types=1);

namespace App\User\Transport\MessageHandler;

use App\Platform\Application\Service\Mailer\Mailer;
use App\Property\Application\Service\Cache\UserDataCache;
use App\User\Domain\Entity\User;
use App\User\Transport\Message\SendEmailConfirmationLink;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Class SendEmailConfirmationLinkHandler
 *
 * @package App\User\Transport\MessageHandler
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[AsMessageHandler]
final readonly class SendEmailConfirmationLinkHandler
{
    use UserDataCache;

    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private Mailer $mailer,
        private UrlGeneratorInterface $router,
        private TranslatorInterface $translator
    ) {
    }

    /**
     * @param SendEmailConfirmationLink $sendEmailConfirmationLink
     *
     * @throws TransportExceptionInterface
     * @throws InvalidArgumentException
     */
    public function __invoke(SendEmailConfirmationLink $sendEmailConfirmationLink): void
    {
        $user = $sendEmailConfirmationLink->getUser();
        $email = $this->buildEmail($user);
        $this->mailer->send($email);
        $this->setConfirmationSentAt($user);
    }

    private function getSender(): Address
    {
        $host = $this->router->getContext()->getHost();

        return new Address('no-reply@'.$host, $host);
    }

    private function getSubject(): string
    {
        return $this->translator->trans('confirmation.email.subject');
    }

    private function getSignatureComponents(User $user): VerifyEmailSignatureComponents
    {
        return $this->verifyEmailHelper->generateSignature(
            'verify_email',
            (string) $user->getId(),
            $user->getEmail()
        );
    }

    private function createContext(VerifyEmailSignatureComponents $signatureComponents): array
    {
        return [
            'signedUrl' => $signatureComponents->getSignedUrl(),
            'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
            'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
        ];
    }

    private function buildEmail(User $user): TemplatedEmail
    {
        $signatureComponents = $this->getSignatureComponents($user);

        return (new TemplatedEmail())
            ->from($this->getSender())
            ->to($user->getEmail())
            ->subject($this->getSubject())
            ->textTemplate('emails/confirmation_email.html.twig')
            ->context($this->createContext($signatureComponents));
    }
}
