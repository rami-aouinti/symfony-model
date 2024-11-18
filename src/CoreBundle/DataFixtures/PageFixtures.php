<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\DataFixtures;

use App\CoreBundle\Component\Utils\CreateDefaultPages;
use App\CoreBundle\Entity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageFixtures extends Fixture
{
    public function __construct(
        private readonly CreateDefaultPages $createDefaultPages,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        /** @var User $admin */
        $admin = $this->getReference(AccessUserFixtures::ADMIN_USER_REFERENCE);
        $url = $this->getReference(AccessUserFixtures::ACCESS_URL_REFERENCE);

        $locale = $this->translator->getLocale();
        $this->createDefaultPages->createDefaultPages($admin, $url, $locale);
    }
}
