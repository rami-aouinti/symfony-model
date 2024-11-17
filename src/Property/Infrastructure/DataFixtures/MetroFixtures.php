<?php

declare(strict_types=1);

namespace App\Property\Infrastructure\DataFixtures;

use App\Place\Domain\Entity\Metro;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class MetroFixtures
 *
 * @package App\Property\Infrastructure\DataFixtures
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class MetroFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getMetroData() as [$city, $name, $slug]) {
            $metro = new Metro();
            $metro->setCity($city);
            $metro->setName($name);
            $metro->setSlug($slug);
            $manager->persist($metro);
            $this->addReference($name, $metro);
        }
        $manager->flush();
    }

    private function getMetroData(): array
    {
        return [
            [$this->getReference('Miami'), 'Government Center', 'government-center'],
            [$this->getReference('Miami'), 'Allapattah', 'allapattah'],
            [$this->getReference('Miami'), 'Brickell', 'brickell'],
            [$this->getReference('Miami'), 'Culmer', 'culmer'],
        ];
    }

    public function getDependencies(): array
    {
        return [
            CityFixtures::class,
        ];
    }
}
