<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\DataFixtures;

use App\Platform\Domain\Entity\ResourceFormat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ResourceFormatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $list = [
            [
                'name' => 'html',
            ],
            [
                'name' => 'txt',
            ],
        ];

        foreach ($list as $key => $data) {
            $resourceFormat = (new ResourceFormat())
                ->setTitle($data['name'])
            ;
            $manager->persist($resourceFormat);
        }

        $manager->flush();
    }
}
