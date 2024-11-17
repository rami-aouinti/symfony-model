<?php

declare(strict_types=1);

namespace App\Platform\Application\Service\Mailer;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Class Mailer
 *
 * @package App\Platform\Application\Service\Mailer
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final readonly class Mailer
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(Email $email): void
    {
        $this->mailer->send($email);
    }
}
