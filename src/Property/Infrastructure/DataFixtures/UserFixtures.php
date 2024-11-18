<?php

declare(strict_types=1);

namespace App\Property\Infrastructure\DataFixtures;

use App\User\Domain\Entity\Profile;
use App\User\Domain\Entity\User;
use App\User\Infrastructure\Transformer\UserTransformer;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @package App\Property\Infrastructure\DataFixtures
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserTransformer $transformer
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$fullName, $username, $phone, $email, $roles]) {
            $user = new User();
            $profile = new Profile();
            $user->setUsername($username);
            $user->setFullName($fullName);
            $user->setPassword($username);
            $user->setEmail($email);
            $user->setRoles($roles);
            $profile->setFullName($fullName)->setPhone('+004911111111');
            $profile->setUser($user);
            $user->setProfile($profile);
            $user->setPasswordRequestedAt(new DateTime('now'));
            $user->setEmailVerifiedAt(new DateTime('now'));
            $user = $this->transformer->transform($user);
            $manager->persist($user);
            $manager->persist($profile);
            $this->addReference($username, $user);
        }
        $manager->flush();
    }

    private function getUserData(): array
    {
        return [
            ['John Smith', 'admin', '0(0)99766899', 'admin@admin.com', ['ROLE_ADMIN', 'ROLE_USER']],
            ['Rhonda Jordan', 'user', '0(0)99766899', 'user@user.com', ['ROLE_USER']],
        ];
    }
}
