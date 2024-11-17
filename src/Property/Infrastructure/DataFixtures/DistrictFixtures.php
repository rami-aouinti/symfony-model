<?php

declare(strict_types=1);

namespace App\Property\Infrastructure\DataFixtures;

use App\Place\Domain\Entity\District;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class DistrictFixtures
 *
 * @package App\Property\Infrastructure\DataFixtures
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class DistrictFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getDistrictData() as [$city, $name, $slug]) {
            $district = new District();
            $district->setCity($city);
            $district->setName($name);
            $district->setSlug($slug);
            $manager->persist($district);
            $this->addReference($name, $district);
        }
        $manager->flush();
    }

    private function getDistrictData(): array
    {
        return [
            [$this->getReference('Tampa'), 'South Tampa', 'south-tampa'],
        ];
    }

    public function getDependencies(): array
    {
        return [
            CityFixtures::class,
        ];
    }
}
