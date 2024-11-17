<?php

declare(strict_types=1);

/*
 * This file is part of the bjoern-hempel/php-calendar-api project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Calendar\Infrastructure\DataFixtures;

use App\Calendar\Application\Service\CalendarBuilderService;
use App\Calendar\Domain\Entity\Calendar;
use App\Calendar\Domain\Entity\CalendarImage;
use App\Calendar\Domain\Entity\CalendarStyle;
use App\Calendar\Domain\Entity\Holiday;
use App\Calendar\Domain\Entity\HolidayGroup;
use App\Event\Domain\Entity\Event;
use App\Media\Application\Utils\ImageProperty;
use App\Media\Domain\Entity\Image;
use App\User\Domain\Entity\Profile;
use App\User\Domain\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Random\RandomException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.1 (2022-11-22)
 * @since 0.1.1 (2022-11-22) Add PHP Magic Number Detector (PHPMND).
 * @since 0.1.0 (2021-12-30) First version.
 * @package App\Calendar\Domain\Entity
 */
class AppFixtures extends Fixture
{
    final public const int INDEX_1 = 1;

    final public const int INDEX_2 = 2;

    final public const string FIXTURE_TEMPLATE_EMAIL = 'user%d@domain.tld';

    final public const string FIXTURE_TEMPLATE_EMAIL_ADMIN = 'admin%d@domain.tld';

    final public const string FIXTURE_TEMPLATE_USERNAME = 'user%d';

    final public const string FIXTURE_TEMPLATE_USERNAME_ADMIN = 'admin%d';

    final public const string FIXTURE_TEMPLATE_PASSWORD = 'password%d';

    final public const string FIXTURE_TEMPLATE_PASSWORD_ADMIN = 'password%d';

    final public const string FIXTURE_TEMPLATE_FIRSTNAME = 'Firstname %d';

    final public const string FIXTURE_TEMPLATE_FIRSTNAME_ADMIN = 'Admin %d';

    final public const string FIXTURE_TEMPLATE_LASTNAME = 'Lastname %d';

    final public const string FIXTURE_TEMPLATE_LASTNAME_ADMIN = 'Admin %d';

    final public const string NAME_HOLIDAY_GROUP_SAXONY = 'Sachsen';

    private const string ENVIRONMENT_NAME_DEV = 'dev';

    private const string ENVIRONMENT_NAME_TEST = 'test';

    /**
     * @var string[][]|int[][]
     */
    protected array $calendars = [
        /* Titel page */
        0 => [
            'sourcePath' => 'source/00.jpg',
            'targetPath' => 'target/00.jpg',
            'title' => 'Las Palmas, Gran Canaria, Spanien, 2021',
            'position' => '28°09’42.9"N 15°26’05.1"W',
            'year' => 2022,
            'month' => 0,
            'valign' => CalendarBuilderService::VALIGN_TOP,
            'url' => 'https://www.google.de',
        ],

        /* 01 */
        1 => [
            'sourcePath' => 'source/01.jpg',
            'targetPath' => 'target/01.jpg',
            'title' => 'Playa de las Canteras, Gran Canaria, Spanien, 2021',
            'position' => '28°08’53.9"N 15°25’53.0"W',
            'year' => 2022,
            'month' => 1,
            'valign' => CalendarBuilderService::VALIGN_TOP,
            'url' => 'https://www.facebook.com',
        ],

        /* 02 */
        2 => [
            'sourcePath' => 'source/02.jpg',
            'targetPath' => 'target/02.jpg',
            'title' => 'Artenara, Gran Canaria, Spanien, 2021',
            'position' => '28°01’03.5"N 15°40’08.4"W',
            'year' => 2022,
            'month' => 2,
            'valign' => CalendarBuilderService::VALIGN_TOP,
            'url' => 'https://www.facebook.com',
        ],

        /* 03 */
        3 => [
            'sourcePath' => 'source/03.jpg',
            'targetPath' => 'target/03.jpg',
            'title' => 'Brännö, Göteborg, Schweden, 2020',
            'position' => '57°38’12.3"N 11°46’02.6"E',
            'year' => 2022,
            'month' => 3,
            'valign' => CalendarBuilderService::VALIGN_TOP,
            'url' => 'https://www.facebook.com',
        ],

        /* 04 */
        4 => [
            'sourcePath' => 'source/04.jpg',
            'targetPath' => 'target/04.jpg',
            'title' => 'Meersburg, Deutschland, 2021',
            'position' => '47°41’36.6"N 9°16’17.6"E',
            'year' => 2022,
            'month' => 4,
            'valign' => CalendarBuilderService::VALIGN_TOP,
            'url' => 'https://www.facebook.com',
        ],

        /* 05 */
        5 => [
            'sourcePath' => 'source/05.jpg',
            'targetPath' => 'target/05.jpg',
            'title' => 'Norra Sjöslingan, Göteborg, Schweden, 2020',
            'position' => '57°41’26.3"N 12°02’10.3"E',
            'year' => 2022,
            'month' => 5,
            'valign' => CalendarBuilderService::VALIGN_TOP,
            'url' => 'https://www.facebook.com',
        ],

        /* 06 */
        6 => [
            'sourcePath' => 'source/06.jpg',
            'targetPath' => 'target/06.jpg',
            'title' => 'Bregenz, Bodensee, Österreich, 2021',
            'position' => '47°30’29.4"N 9°45’31.6"E',
            'year' => 2022,
            'month' => 6,
            'valign' => CalendarBuilderService::VALIGN_BOTTOM,
            'url' => 'https://www.facebook.com',
        ],

        /* 07 */
        7 => [
            'sourcePath' => 'source/07.jpg',
            'targetPath' => 'target/07.jpg',
            'title' => 'Badi Triboltingen, Triboltingen, Schweiz, 2021',
            'position' => '47°39’57.2"N 9°06’37.9"E',
            'year' => 2022,
            'month' => 7,
            'valign' => CalendarBuilderService::VALIGN_TOP,
            'url' => 'https://www.facebook.com',
        ],

        /* 08 */
        8 => [
            'sourcePath' => 'source/08.jpg',
            'targetPath' => 'target/08.jpg',
            'title' => 'Zürich, Schweiz, 2021',
            'position' => '47°22’22.9"N 8°32’29.0"E',
            'year' => 2022,
            'month' => 8,
            'valign' => CalendarBuilderService::VALIGN_BOTTOM,
            'url' => 'https://www.facebook.com',
        ],

        /* 09 */
        9 => [
            'sourcePath' => 'source/09.jpg',
            'targetPath' => 'target/09.jpg',
            'title' => 'Stein am Rhein, Schweiz, 2021',
            'position' => '47°39’37.2"N 8°51’30.6"E',
            'year' => 2022,
            'month' => 9,
            'valign' => CalendarBuilderService::VALIGN_TOP,
            'url' => 'https://www.facebook.com',
        ],

        /* 10 */
        10 => [
            'sourcePath' => 'source/10.jpg',
            'targetPath' => 'target/10.jpg',
            'title' => 'Insel Mainau, Bodensee, Deutschland, 2021',
            'position' => '47°42’17.5"N 9°11’37.7"E',
            'year' => 2022,
            'month' => 10,
            'valign' => CalendarBuilderService::VALIGN_BOTTOM,
            'url' => 'https://www.facebook.com',
        ],

        /* 11 */
        11 => [
            'sourcePath' => 'source/11.jpg',
            'targetPath' => 'target/11.jpg',
            'title' => 'Casa Milà, Barcelona, Spanien, 2020',
            'position' => '41°23’43.2"N 2°09’42.4"E',
            'year' => 2022,
            'month' => 11,
            'valign' => CalendarBuilderService::VALIGN_TOP,
            'url' => 'https://www.facebook.com',
        ],

        /* 12 */
        12 => [
            'sourcePath' => 'source/12.jpg',
            'targetPath' => 'target/12.jpg',
            'title' => 'Meersburg, Deutschland, 2021',
            'position' => '47°41’39.0"N 9°16’15.2"E',
            'year' => 2022,
            'month' => 12,
            'valign' => CalendarBuilderService::VALIGN_BOTTOM,
            'url' => 'https://www.facebook.com',
        ],
    ];

    /**
     * @var array<int, array<string, array<int, array<int, string>>|string>>
     */
    protected array $holidayDatas = [
        [
            'name' => 'Baden-Württemberg',
            'name_short' => 'BW',
            'holidays' => [
            ],
        ],
        [
            'name' => 'Bayern',
            'name_short' => 'BY',
            'holidays' => [
                ['Neujahr', '2022-01-01T12:00:00Z'],
                ['Heilige Drei Könige', '2022-01-06T12:00:00Z'],
                ['Karfreitag', '2022-04-15T12:00:00Z'],
                ['Ostermontag', '2022-04-18T12:00:00Z'],
                ['Tag der Arbeit', '2022-05-01T12:00:00Z'],
                ['Christi Himmelfahrt', '2022-05-26T12:00:00Z'],
                ['Pfingstmontag ', '2022-06-06T12:00:00Z'],
                ['Fronleichnam ', '2022-06-16T12:00:00Z'],
                ['Augsburger Friedensfest ', '2022-08-08T12:00:00Z'],
                ['Mariä Himmelfahrt ', '2022-08-15T12:00:00Z'],
                ['Tag der Deutschen Einheit', '2022-10-03T12:00:00Z'],
                ['Allerheiligen', '2022-11-01T12:00:00Z'],
                ['1. Weihnachtsfeiertag', '2022-12-25T12:00:00Z'],
                ['2. Weihnachtsfeiertag', '2022-12-26T12:00:00Z'],
            ],
        ],
        [
            'name' => 'Berlin',
            'name_short' => 'BE',
            'holidays' => [
                ['Neujahr', '2022-01-01T12:00:00Z'],
                ['Internationaler Frauentag', '2022-03-08T12:00:00Z'],
                ['Karfreitag', '2022-04-15T12:00:00Z'],
                ['Ostermontag', '2022-04-18T12:00:00Z'],
                ['Tag der Arbeit', '2022-05-01T12:00:00Z'],
                ['Christi Himmelfahrt', '2022-05-26T12:00:00Z'],
                ['Pfingstmontag ', '2022-06-06T12:00:00Z'],
                ['Tag der Deutschen Einheit', '2022-10-03T12:00:00Z'],
                ['1. Weihnachtsfeiertag', '2022-12-25T12:00:00Z'],
                ['2. Weihnachtsfeiertag', '2022-12-26T12:00:00Z'],
            ],
        ],
        [
            'name' => 'Brandenburg',
            'name_short' => 'BB',
            'holidays' => [
                ['Neujahr', '2022-01-01T12:00:00Z'],
                ['Karfreitag', '2022-04-15T12:00:00Z'],
                ['Ostermontag', '2022-04-18T12:00:00Z'],
                ['Tag der Arbeit', '2022-05-01T12:00:00Z'],
                ['Christi Himmelfahrt', '2022-05-26T12:00:00Z'],
                ['Pfingstmontag ', '2022-06-06T12:00:00Z'],
                ['Tag der Deutschen Einheit', '2022-10-03T12:00:00Z'],
                ['Reformationstag', '2022-10-31T12:00:00Z'],
                ['1. Weihnachtsfeiertag', '2022-12-25T12:00:00Z'],
                ['2. Weihnachtsfeiertag', '2022-12-26T12:00:00Z'],
            ],
        ],
        [
            'name' => 'Bremen',
            'name_short' => 'HB',
            'holidays' => [
                ['Neujahr', '2022-01-01T12:00:00Z'],
                ['Karfreitag', '2022-04-15T12:00:00Z'],
                ['Ostermontag', '2022-04-18T12:00:00Z'],
                ['Tag der Arbeit', '2022-05-01T12:00:00Z'],
                ['Christi Himmelfahrt', '2022-05-26T12:00:00Z'],
                ['Pfingstmontag ', '2022-06-06T12:00:00Z'],
                ['Tag der Deutschen Einheit', '2022-10-03T12:00:00Z'],
                ['Reformationstag', '2022-10-31T12:00:00Z'],
                ['1. Weihnachtsfeiertag', '2022-12-25T12:00:00Z'],
                ['2. Weihnachtsfeiertag', '2022-12-26T12:00:00Z'],
            ],
        ],
        [
            'name' => 'Hamburg',
            'name_short' => 'HH',
            'holidays' => [
                ['Neujahr', '2022-01-01T12:00:00Z'],
                ['Karfreitag', '2022-04-15T12:00:00Z'],
                ['Ostermontag', '2022-04-18T12:00:00Z'],
                ['Tag der Arbeit', '2022-05-01T12:00:00Z'],
                ['Christi Himmelfahrt', '2022-05-26T12:00:00Z'],
                ['Pfingstmontag ', '2022-06-06T12:00:00Z'],
                ['Tag der Deutschen Einheit', '2022-10-03T12:00:00Z'],
                ['Reformationstag', '2022-10-31T12:00:00Z'],
                ['1. Weihnachtsfeiertag', '2022-12-25T12:00:00Z'],
                ['2. Weihnachtsfeiertag', '2022-12-26T12:00:00Z'],
            ],
        ],
        [
            'name' => 'Hessen',
            'name_short' => 'HE',
            'holidays' => [
                ['Neujahr', '2022-01-01T12:00:00Z'],
                ['Karfreitag', '2022-04-15T12:00:00Z'],
                ['Ostermontag', '2022-04-18T12:00:00Z'],
                ['Tag der Arbeit', '2022-05-01T12:00:00Z'],
                ['Christi Himmelfahrt', '2022-05-26T12:00:00Z'],
                ['Pfingstmontag ', '2022-06-06T12:00:00Z'],
                ['Fronleichnam', '2022-06-16T12:00:00Z'],
                ['Tag der Deutschen Einheit', '2022-10-03T12:00:00Z'],
                ['1. Weihnachtsfeiertag', '2022-12-25T12:00:00Z'],
                ['2. Weihnachtsfeiertag', '2022-12-26T12:00:00Z'],
            ],
        ],
        [
            'name' => 'Mecklenburg-Vorpommern',
            'name_short' => 'MV',
            'holidays' => [
                ['Neujahr', '2022-01-01T12:00:00Z'],
                ['Karfreitag', '2022-04-15T12:00:00Z'],
                ['Ostermontag', '2022-04-18T12:00:00Z'],
                ['Tag der Arbeit', '2022-05-01T12:00:00Z'],
                ['Christi Himmelfahrt', '2022-05-26T12:00:00Z'],
                ['Pfingstmontag ', '2022-06-06T12:00:00Z'],
                ['Tag der Deutschen Einheit', '2022-10-03T12:00:00Z'],
                ['Reformationstag', '2022-10-31T12:00:00Z'],
                ['1. Weihnachtsfeiertag', '2022-12-25T12:00:00Z'],
                ['2. Weihnachtsfeiertag', '2022-12-26T12:00:00Z'],
            ],
        ],
        [
            'name' => 'Niedersachsen',
            'name_short' => 'NI',
            'holidays' => [
                ['Neujahr', '2022-01-01T12:00:00Z'],
                ['Karfreitag', '2022-04-15T12:00:00Z'],
                ['Ostermontag', '2022-04-18T12:00:00Z'],
                ['Tag der Arbeit', '2022-05-01T12:00:00Z'],
                ['Christi Himmelfahrt', '2022-05-26T12:00:00Z'],
                ['Pfingstmontag ', '2022-06-06T12:00:00Z'],
                ['Tag der Deutschen Einheit', '2022-10-03T12:00:00Z'],
                ['Reformationstag', '2022-10-31T12:00:00Z'],
                ['1. Weihnachtsfeiertag', '2022-12-25T12:00:00Z'],
                ['2. Weihnachtsfeiertag', '2022-12-26T12:00:00Z'],
            ],
        ],
        [
            'name' => 'Nordrhein-Westfalen',
            'name_short' => 'NW',
            'holidays' => [
                ['Neujahr', '2022-01-01T12:00:00Z'],
                ['Karfreitag', '2022-04-15T12:00:00Z'],
                ['Ostermontag', '2022-04-18T12:00:00Z'],
                ['Tag der Arbeit', '2022-05-01T12:00:00Z'],
                ['Christi Himmelfahrt', '2022-05-26T12:00:00Z'],
                ['Pfingstmontag ', '2022-06-06T12:00:00Z'],
                ['Fronleichnam', '2022-06-16T12:00:00Z'],
                ['Tag der Deutschen Einheit', '2022-10-03T12:00:00Z'],
                ['Allerheiligen', '2022-10-31T12:00:00Z'],
                ['1. Weihnachtsfeiertag', '2022-12-25T12:00:00Z'],
                ['2. Weihnachtsfeiertag', '2022-12-26T12:00:00Z'],
            ],
        ],
        [
            'name' => 'Rheinland-Pfalz',
            'name_short' => 'RP',
            'holidays' => [
                ['Neujahr', '2022-01-01T12:00:00Z'],
                ['Karfreitag', '2022-04-15T12:00:00Z'],
                ['Ostermontag', '2022-04-18T12:00:00Z'],
                ['Tag der Arbeit', '2022-05-01T12:00:00Z'],
                ['Christi Himmelfahrt', '2022-05-26T12:00:00Z'],
                ['Pfingstmontag ', '2022-06-06T12:00:00Z'],
                ['Fronleichnam', '2022-06-16T12:00:00Z'],
                ['Tag der Deutschen Einheit', '2022-10-03T12:00:00Z'],
                ['Allerheiligen', '2022-10-31T12:00:00Z'],
                ['1. Weihnachtsfeiertag', '2022-12-25T12:00:00Z'],
                ['2. Weihnachtsfeiertag', '2022-12-26T12:00:00Z'],
            ],
        ],
        [
            'name' => 'Saarland',
            'name_short' => 'SL',
            'holidays' => [
                ['Neujahr', '2022-01-01T12:00:00Z'],
                ['Karfreitag', '2022-04-15T12:00:00Z'],
                ['Ostermontag', '2022-04-18T12:00:00Z'],
                ['Tag der Arbeit', '2022-05-01T12:00:00Z'],
                ['Christi Himmelfahrt', '2022-05-26T12:00:00Z'],
                ['Pfingstmontag ', '2022-06-06T12:00:00Z'],
                ['Fronleichnam', '2022-06-16T12:00:00Z'],
                ['Tag der Deutschen Einheit', '2022-10-03T12:00:00Z'],
                ['Allerheiligen', '2022-10-31T12:00:00Z'],
                ['1. Weihnachtsfeiertag', '2022-12-25T12:00:00Z'],
                ['2. Weihnachtsfeiertag', '2022-12-26T12:00:00Z'],
            ],
        ],
        [
            'name' => 'Sachsen',
            'name_short' => 'SN',
            'holidays' => [
                ['Neujahr', '2022-01-01T12:00:00Z'],
                ['Karfreitag', '2022-04-15T12:00:00Z'],
                ['Ostermontag', '2022-04-18T12:00:00Z'],
                ['Tag der Arbeit', '2022-05-01T12:00:00Z'],
                ['Christi Himmelfahrt', '2022-05-26T12:00:00Z'],
                ['Pfingstmontag ', '2022-06-06T12:00:00Z'],
                ['Tag der Deutschen Einheit', '2022-10-03T12:00:00Z'],
                ['Reformationstag', '2022-10-31T12:00:00Z'],
                ['Buß- und Bettag', '2022-11-16T12:00:00Z'],
                ['1. Weihnachtsfeiertag', '2022-12-25T12:00:00Z'],
                ['2. Weihnachtsfeiertag', '2022-12-26T12:00:00Z'],
            ],
        ],
        [
            'name' => 'Sachsen-Anhalt',
            'name_short' => 'ST',
            'holidays' => [
            ],
        ],
        [
            'name' => 'Schleswig-Holstein',
            'name_short' => 'SH',
            'holidays' => [
            ],
        ],
        [
            'name' => 'Thüringen',
            'name_short' => 'TH',
            'holidays' => [
            ],
        ],
    ];

    /**
     * @var string[][]|int[][]
     */
    protected array $eventDatas = [
        ['Angela Merkel', '1954-07-17T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Arnold Schwarzenegger', '1947-07-30T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Bernhard', '2100-12-25T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Björn', '1980-02-02T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Carolin Kebekus', '1980-05-09T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Daniel Radcliffe', '1989-07-23T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Erik', '1970-09-11T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Isabel', '1994-08-18T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Heike', '1970-05-06T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Manuel Neuer', '1986-03-27T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Olaf Scholz', '1958-06-14T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Otto Waalkes', '1948-07-22T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Rico', '2100-08-18T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Sebastian', '1997-05-22T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Sido', '1980-11-30T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['Elisabeth II.', '1926-04-21T12:00:00Z', CalendarBuilderService::EVENT_TYPE_BIRTHDAY],
        ['New York City Marathon', '2022-11-06T12:00:00Z', CalendarBuilderService::EVENT_TYPE_EVENT],
        ['Zrce Spring Break, Croatia', '2022-06-03T12:00:00Z', CalendarBuilderService::EVENT_TYPE_EVENT_GROUP],
    ];

    private ?ObjectManager $manager = null;

    private ContainerInterface $container;

    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly ImageProperty $imageProperty,
        ContainerInterface $container,
        ObjectManager $manager = null
    ) {
        $this->container = $container;
        if ($manager !== null) {
            $this->setManager($manager);
        }
    }

    /**
     * Set ObjectManager.
     */
    public function setManager(ObjectManager $manager): void
    {
        $this->manager = $manager;
    }

    /**
     * Get hash from given (user) id.
     */
    public function getHash(int $i, bool $admin = false): string
    {
        $salt = 'S4Lt';

        return match (true) {
            !$admin && $i === self::INDEX_1 => 'cf6b37d2b5f805a0f76ef2b3610eff7a705a2291',
            !$admin && $i === self::INDEX_2 => 'da4b9237bacccdf19c0760cab7aec4a8359010b1',
            $admin && $i === self::INDEX_1 => '9cc28538cd413685762993a2376412393be29cce',
            $admin && $i === self::INDEX_2 => '8768be4811c6bc1df185440b82b41aeca048f318',
            default => sha1(sprintf('%s-%s', $salt, $i)),
        };
    }

    /**
     * Returns a fixture email.
     */
    public static function getEmail(int $i, bool $admin = false): string
    {
        return sprintf($admin ? self::FIXTURE_TEMPLATE_EMAIL_ADMIN : self::FIXTURE_TEMPLATE_EMAIL, $i);
    }

    /**
     * Returns a fixture username.
     */
    public static function getUsername(int $i, bool $admin = false): string
    {
        return sprintf($admin ? self::FIXTURE_TEMPLATE_USERNAME_ADMIN : self::FIXTURE_TEMPLATE_USERNAME, $i);
    }

    /**
     * Returns a fixture password.
     */
    public static function getPassword(int $i, bool $admin = false): string
    {
        return sprintf($admin ? self::FIXTURE_TEMPLATE_PASSWORD_ADMIN : self::FIXTURE_TEMPLATE_PASSWORD, $i);
    }

    /**
     * Returns a fixture firstname.
     */
    public static function getFirstname(int $i, bool $admin = false): string
    {
        return sprintf($admin ? self::FIXTURE_TEMPLATE_FIRSTNAME_ADMIN : self::FIXTURE_TEMPLATE_FIRSTNAME, $i);
    }

    /**
     * Returns a fixture lastname.
     */
    public static function getLastname(int $i, bool $admin = false): string
    {
        return sprintf($admin ? self::FIXTURE_TEMPLATE_LASTNAME_ADMIN : self::FIXTURE_TEMPLATE_LASTNAME, $i);
    }

    /**
     * Returns user roles.
     *
     * @return string[]
     */
    public static function getRoles(bool $admin = false): array
    {
        return $admin ? [User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN] : [User::ROLE_USER];
    }

    /**
     * Returns a user as JSON.
     *
     * @return array{id: int, email: string, username: string, firstname: string, lastname: string, roles: string[]}
     */
    #[ArrayShape([
        'id' => 'int',
        'email' => 'string',
        'username' => 'string',
        'firstname' => 'string',
        'lastname' => 'string',
        'roles' => 'array',
    ])]
    #[Pure]
    public static function getUserAsJson(int $i, bool $admin = false): array
    {
        return [
            'id' => $admin ? $i + 2 : $i,
            'email' => self::getEmail($i, $admin),
            'username' => self::getUsername($i, $admin),
            'firstname' => self::getFirstname($i, $admin),
            'lastname' => self::getLastname($i, $admin),
            'roles' => self::getRoles($admin),
        ];
    }

    /**
     * Returns a HolidayGroup resource with its Holiday events.
     *
     * @return array<int|string, HolidayGroup>
     * @throws Exception
     */
    public function getHolidayGroups(): array
    {
        $holidayGroups = [];

        foreach ($this->holidayDatas as $holidayDatas) {
            /* Get persisted public holiday group */
            $holidayGroup = $this->setHolidayGroup(strval($holidayDatas['name']), strval($holidayDatas['name_short']));

            if ($this->getEnvironment() === self::ENVIRONMENT_NAME_TEST && array_key_exists('holidays', $holidayDatas)) {
                if (!is_array($holidayDatas['holidays'])) {
                    throw new Exception(sprintf('Array expected (%s:%d).', __FILE__, __LINE__));
                }

                foreach ($holidayDatas['holidays'] as $holiday) {
                    $this->setHoliday($holidayGroup, $holiday[0], $holiday[1]);
                }
            }

            $holidayGroups[strval($holidayDatas['name'])] = $holidayGroup;
        }

        return $holidayGroups;
    }

    /**
     * Returns a CalendarStyle resource.
     */
    public function getCalendarStyle(): CalendarStyle
    {
        return $this->setCalendarStyle();
    }

    /**
     * Returns a User resource.
     *
     * @throws Exception
     */
    public function getUser(CalendarStyle $calendarStyle, HolidayGroup $holidayGroup, int $i = 1, bool $admin = false): User
    {
        $user = $this->setUser($i, $admin);

        /* Add events to user */
        foreach ($this->eventDatas as $eventData) {
            $this->setEvent($user, strval($eventData[0]), intval($eventData[2]), strval($eventData[1]));
        }

        /* Create calendar for user */
        $calendar = $this->setCalendar($user, $calendarStyle, $holidayGroup);

        foreach ($this->calendars as $calendarData) {
            /* Create image */
            $image = $this->setImage($user, $this->getSourcePath($user, $calendarData));

            /* Connect calendar with image */
            $this->setCalendarImage(
                $user,
                $calendar,
                $image,
                intval($calendarData['year']),
                intval($calendarData['month']),
                strval($calendarData['title']),
                strval($calendarData['position']),
                intval($calendarData['valign']),
                strval($calendarData['url'])
            );
        }

        return $user;
    }

    /**
     * Load fixtures.
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        /* Check environment (only dev or test allowed). */
        if (!in_array($this->getEnvironment(), [self::ENVIRONMENT_NAME_DEV, self::ENVIRONMENT_NAME_TEST])) {
            throw new Exception(sprintf('Illegal environment "%s" (%s:%d).', $this->getEnvironment(), __FILE__, __LINE__));
        }

        /* Set ObjectManager */
        $this->setManager($manager);

        /* Get and create HolidayGroup resources. */
        $holidayGroups = $this->getHolidayGroups();

        /* Get and create CalendarStyle resource. */
        $calendarStyle = $this->getCalendarStyle();

        /* Create User resources. */
        for ($i = self::INDEX_1; $i <= self::INDEX_2; $i++) {
            $this->getUser($calendarStyle, $holidayGroups[self::NAME_HOLIDAY_GROUP_SAXONY], $i);
        }

        /* Create User resources. */
        for ($i = self::INDEX_1; $i <= self::INDEX_2; $i++) {
            $this->getUser($calendarStyle, $holidayGroups[self::NAME_HOLIDAY_GROUP_SAXONY], $i, true);
        }

        /* Save all resources to db. */
        $manager->flush();
    }

    /**
     * Sets the container.
     *
     * @throws Exception
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        /* Check container */
        if ($container === null) {
            throw new Exception(sprintf('Container is missing (%s:%d).', __FILE__, __LINE__));
        }

        $this->container = $container;
    }

    /**
     * Returns the container.
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Returns the kernel.
     *
     * @throws Exception
     */
    public function getKernel(): KernelInterface
    {
        $kernel = $this->container->get('kernel');

        if (!$kernel instanceof KernelInterface) {
            throw new Exception(sprintf('Kernel class expected (%s:%d)', __FILE__, __LINE__));
        }

        return $kernel;
    }

    /**
     * Gets the environment.
     *
     * @throws Exception
     */
    public function getEnvironment(): string
    {
        return $this->getKernel()->getEnvironment();
    }

    /**
     * Sets a Holiday resource.
     *
     * @throws Exception
     */
    protected function setHoliday(HolidayGroup $holidayGroup, string $name, string $date): Holiday
    {
        $holiday = new Holiday();
        $holiday->setHolidayGroup($holidayGroup);
        $holiday->setName($name);
        $holiday->setDate(new DateTime($date));
        $holiday->setType(0);
        $holiday->setConfig([
            'color' => '255,255,255,100',
        ]);
        $this->manager?->persist($holiday);

        return $holiday;
    }

    /**
     * Sets a HolidayGroup resource.
     */
    protected function setHolidayGroup(string $name, string $nameShort): HolidayGroup
    {
        $holidayGroup = new HolidayGroup();
        $holidayGroup->setName($name);
        $holidayGroup->setNameShort($nameShort);
        $this->manager?->persist($holidayGroup);

        return $holidayGroup;
    }

    /**
     * Sets a CalendarStyle resource.
     */
    protected function setCalendarStyle(): CalendarStyle
    {
        $calendarStyle = new CalendarStyle();
        $calendarStyle->setName('default');
        $calendarStyle->setConfig([
            'name' => 'default',
        ]);
        $this->manager?->persist($calendarStyle);

        return $calendarStyle;
    }

    /**
     * Gets source path of image.
     *
     * @param string[]|int[] $calendarData
     *
     * @throws RandomException
     */
    protected function getSourcePath(User $user, array $calendarData): string
    {
        return sprintf('%s/%s', $user->getIdHash(), strval($calendarData['sourcePath']));
    }

    /**
     * Sets a User resource.
     *
     * @throws RandomException
     */
    protected function setUser(int $i = 1, bool $admin = false): User
    {
        /* Create credentials. */
        $email = self::getEmail($i, $admin);
        $username = self::getUsername($i, $admin);
        $password = self::getPassword($i, $admin);
        $profile = new Profile();
        /* Create a new user. */
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
        $user->setFirstname(self::getFirstname($i, $admin));
        $user->setFullName(self::getFirstname($i, $admin) . ' ' . self::getLastname($i, $admin));
        $user->setLastname(self::getLastname($i, $admin));
        $user->setIdHash($this->getHash($i, $admin));
        $user->setRoles(self::getRoles($admin));
        $profile->setFullName($user->getFullName())->setPhone('+004911111111');
        $profile->setUser($user);
        $user->setProfile($profile);
        $user->setPasswordRequestedAt(new DateTime('now'));
        $user->setEmailVerifiedAt(new DateTime('now'));

        $this->manager?->persist($user);
        $this->manager?->persist($profile);

        /* Return the user */
        return $user;
    }

    /**
     * Sets a Event resource.
     *
     * @throws Exception
     */
    protected function setEvent(User $user, string $name, int $type, string $date): Event
    {
        $event = new Event();
        $event->setUser($user);
        $event->setName($name);
        $event->setType($type);
        $event->setDate(new DateTime($date));
        $event->setConfig([
            'color' => '255,255,255,100',
        ]);
        $this->manager?->persist($event);

        return $event;
    }

    /**
     * Sets a Calendar resource.
     *
     * @throws Exception
     */
    protected function setCalendar(User $user, CalendarStyle $calendarStyle, HolidayGroup $holidayGroup): Calendar
    {
        $calendar = new Calendar();
        $calendar->setUser($user);
        $calendar->setCalendarStyle($calendarStyle);
        $calendar->setHolidayGroup($holidayGroup);
        $calendar->setName(sprintf('Calendar %d', 1));
        $calendar->setTitle('2022');
        $calendar->setSubtitle('With love - Isa & Björn');
        $calendar->setConfig([
            'backgroundColor' => '255,255,255,100',
            'printCalendarWeek' => true,
            'printWeekNumber' => true,
            'printQrCodeMonth' => true,
            'printQrCodeTitle' => true,
            'aspectRatio' => round(sqrt(2), 3), /* 1:1.414 */
            'height' => $this->getEnvironment() === self::ENVIRONMENT_NAME_TEST ? 800 : 4000,
        ]);
        $this->manager?->persist($calendar);

        return $calendar;
    }

    /**
     * Sets an Image resource.
     *
     * @throws Exception
     */
    protected function setImage(User $user, string $sourcePath): Image
    {
        $image = new Image();
        $image->setUser($user);
        $image->setPath($sourcePath);
        $this->imageProperty->init($user, $image, $this->getEnvironment() === self::ENVIRONMENT_NAME_TEST);
        $this->manager?->persist($image);

        return $image;
    }

    /**
     * Return a CalendarImage resource.
     *
     * @throws Exception
     */
    protected function setCalendarImage(User $user, Calendar $calendar, Image $image, int $year, int $month, string $title, string $position, int $valign, string $url): CalendarImage
    {
        $calendarImage = new CalendarImage();
        $calendarImage->setUser($user);
        $calendarImage->setCalendar($calendar);
        $calendarImage->setImage($image);
        $calendarImage->setYear($year);
        $calendarImage->setMonth($month);
        $calendarImage->setTitle($title);
        $calendarImage->setPosition($position);
        $calendarImage->setUrl($url);
        $calendarImage->setConfig([
            'valign' => $valign,
        ]);
        $this->manager?->persist($calendarImage);

        return $calendarImage;
    }
}
