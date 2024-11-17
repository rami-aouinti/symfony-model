<?php

declare(strict_types=1);

namespace App\User\Transport\Message;


use App\User\Domain\Entity\User;

/**
 * Class SendResetPasswordLink
 *
 * @package App\User\Transport\Message
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final readonly class SendResetPasswordLink
{
    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
