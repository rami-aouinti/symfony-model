<?php

declare(strict_types=1);

namespace App\Platform\Application\Utils;

use DateTime;
use Symfony\Component\Form\FormInterface;

/**
 * @package App\Platform\Application\Utils
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class UserFormDataSelector
{
    public function getEmailVerified(FormInterface $form): bool
    {
        return $form->get('email_verified')->getNormData();
    }

    public function getEmailVerifiedAt(FormInterface $form): ?DateTime
    {
        return $this->getEmailVerified($form)
            ? new DateTime('now')
            : null;
    }
}
