<?php

declare(strict_types=1);

namespace App\Platform\Application\Service;

use App\Platform\Application\Service\Interfaces\MailerServiceInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @package App\Platform\Application\Service
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
readonly class MailerService implements MailerServiceInterface
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function sendMail(string $title, string $from, string $to, string $body): void
    {
        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($title)
            ->html($body);

        $this->mailer->send($email);
    }
}
