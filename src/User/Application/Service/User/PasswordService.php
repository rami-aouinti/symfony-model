<?php

declare(strict_types=1);

namespace App\User\Application\Service\User;

use App\Admin\Application\Service\UserService;
use App\User\Domain\Entity\User;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function count;

/**
 * @package App\User\Application\Service\User
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final readonly class PasswordService
{
    public function __construct(
        private UserService $service,
        private TokenStorageInterface $tokenStorage,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * @throws Exception
     */
    public function update(Request $request): void
    {
        $violations = $this->findViolations($request);

        if (count($violations) > 0) {
            throw new Exception($violations[0]->getMessage());
        }

        /** @var User $user * */
        $user = $this->tokenStorage->getToken()->getUser();
        $user->setPassword($request->get('password1'));
        $this->service->update($user);
    }

    private function findViolations(Request $request): ConstraintViolationListInterface
    {
        $password1 = $this->validator->validate($request->get('password1'), [
            new Assert\Length(null, 10),
        ]);

        $password2 = $this->validator->validate($request->get('password2'), [
            new Assert\EqualTo($request->get('password1'), null, "Passwords Don't Match"),
        ]);

        return count($password1) > 0 ? $password1 : $password2;
    }
}
