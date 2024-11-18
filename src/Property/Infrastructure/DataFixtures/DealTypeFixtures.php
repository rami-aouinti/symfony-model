<?php

declare(strict_types=1);

namespace App\Property\Infrastructure\DataFixtures;

use App\Property\Domain\Entity\DealType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @package App\Property\Infrastructure\DataFixtures
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class DealTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getDealTypeData() as [$slug, $name]) {
            $dealType = new DealType();
            $dealType->setName($name);
            $dealType->setSlug($slug);
            $manager->persist($dealType);
            $this->addReference($name, $dealType);
        }
        $manager->flush();
    }

    private function getDealTypeData(): array
    {
        return [
            ['rent', 'Rent'],
            ['sale', 'Sale'],
        ];
    }
}
