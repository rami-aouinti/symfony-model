<?php

declare(strict_types=1);

namespace App\Property\Infrastructure\DataFixtures;

use App\Platform\Domain\Entity\Menu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @package App\Property\Infrastructure\DataFixtures
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class MenuFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getMenuData() as [$title, $url, $locale]) {
            $menu = new Menu();
            $menu->setTitle($title);
            $menu->setUrl($url);
            $menu->setLocale($locale);
            $manager->persist($menu);
            $this->addReference($title, $menu);
        }
        $manager->flush();
    }

    private function getMenuData(): array
    {
        return [
            ['Homepage', '/', 'en'],
            ['About Us', '/info/about-us', 'en'],
            ['Contact', '/info/contact', 'en'],
            ['Начало', '/', 'bg'],
            ['За нас', '/info/about-us', 'bg'],
            ['Контакти', '/info/contact', 'bg'],
            ['Source Code', 'https://github.com/Coderberg/ResidenceCMS', 'en'],
        ];
    }
}
