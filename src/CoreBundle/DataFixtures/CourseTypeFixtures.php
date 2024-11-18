<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\DataFixtures;

use App\CoreBundle\Entity\Course\CourseType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourseTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $list = [
            'All tools',
            'Entry exam',
        ];

        foreach ($list as $name) {
            $courseType = (new CourseType())
                ->setTitle($name)
            ;
            $manager->persist($courseType);
        }
        $manager->flush();
    }
}
