<?php

declare(strict_types=1);

namespace App\User\Transport\MessageHandler;

use App\Platform\Application\Service\Mailer\Mailer;
use App\User\Transport\Message\SendFeedback;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @package App\User\Transport\MessageHandler
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[AsMessageHandler]
final readonly class SendFeedbackHandler
{
    public function __construct(
        private Mailer $mailer,
        private TranslatorInterface $translator
    ) {
    }

    public function __invoke(SendFeedback $sendFeedback): void
    {
        $feedback = $sendFeedback->getFeedback();

        $subject = $this->translator->trans('email.new_message');

        $email = (new Email())
            ->from(new Address($feedback->getFromEmail(), $feedback->getFromName()))
            ->to($feedback->getToEmail())
            ->replyTo($feedback->getFromEmail())
            ->subject($subject)
            ->text($feedback->getMessage())
            ->html($feedback->getMessage());

        $this->mailer->send($email);
    }
}
