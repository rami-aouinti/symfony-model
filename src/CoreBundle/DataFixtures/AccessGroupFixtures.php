<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\DataFixtures;

use App\CoreBundle\Repository\GroupRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class AccessGroupFixtures
 *
 * @package App\CoreBundle\DataFixtures
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class AccessGroupFixtures extends Fixture
{
    public function __construct(
        private readonly GroupRepository $groupRepository
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->groupRepository->createDefaultGroups($this);
    }
}
