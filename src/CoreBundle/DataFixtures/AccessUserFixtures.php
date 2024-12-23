<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\DataFixtures;

use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Repository\Node\UserRepository;
use App\CoreBundle\Tool\ToolChain;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class AccessUserFixtures
 *
 * @package App\CoreBundle\DataFixtures
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class AccessUserFixtures extends Fixture
{
    public const string ADMIN_USER_REFERENCE = 'admin';
    public const string ANON_USER_REFERENCE = 'anon';
    public const string ACCESS_URL_REFERENCE = 'accessUrl';

    public function __construct(
        private readonly ToolChain $toolChain,
        private readonly UserRepository $userRepository
    ) {}

    public function load(ObjectManager $manager): void
    {
        $timezone = 'Europe\Paris';
        $this->toolChain->createTools();

        // Defined in AccessGroupFixtures.php.
        // $group = $this->getReference('GROUP_ADMIN');

        $admin = (new User())
            ->setSkipResourceNode(true)
            ->setLastname('Doe')
            ->setFirstname('Joe')
            ->setUsername('admin')
            ->setStatus(1)
            ->setPlainPassword('admin')
            ->setEmail('admin@example.org')
            ->setOfficialCode('ADMIN')
            ->setCreatorId(1)
            ->setTimezone($timezone)
            ->addUserAsAdmin()
            ->addRole('ROLE_GLOBAL_ADMIN') // Only for the first user
            // ->addGroup($group)
        ;

        $manager->persist($admin);

        $this->userRepository->updateUser($admin);

        $anon = (new User())
            ->setSkipResourceNode(true)
            ->setLastname('Joe')
            ->setFirstname('Anonymous')
            ->setUsername('anon')
            ->setStatus(ANONYMOUS)
            ->setPlainPassword('anon')
            ->setEmail('anonymous@localhost')
            ->setOfficialCode('anonymous')
            ->setCreatorId(1)
            ->setTimezone($timezone)
        ;
        $manager->persist($anon);

        $fallbackUser = new User();
        $fallbackUser
            ->setSkipResourceNode(true)
            ->setUsername('fallback_user')
            ->setEmail('fallback@example.com')
            ->setPlainPassword('fallback_user')
            ->setStatus(User::ROLE_FALLBACK)
            ->setLastname('Fallback')
            ->setFirstname('User')
            ->setCreatorId(1)
            ->setOfficialCode('FALLBACK')
            ->setAuthSource(PLATFORM_AUTH_SOURCE)
            ->setPhone('0000000000')
            ->setTimezone($timezone)
            ->setActive(USER_SOFT_DELETED)
        ;
        $manager->persist($fallbackUser);

        $manager->flush();

        $this->userRepository->addUserToResourceNode($admin->getId(), $admin->getId());
        $this->userRepository->addUserToResourceNode($anon->getId(), $admin->getId());
        $this->userRepository->addUserToResourceNode($fallbackUser->getId(), $admin->getId());

        $manager->flush();

        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);
        $this->addReference(self::ANON_USER_REFERENCE, $anon);
    }
}
