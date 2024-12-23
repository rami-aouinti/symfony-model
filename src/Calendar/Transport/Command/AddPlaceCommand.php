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

namespace App\Calendar\Transport\Command;

use App\Place\Application\Service\Entity\PlaceLoaderService;
use App\Place\Application\Service\LocationDataService;
use App\Place\Infrastructure\DataType\Point;
use App\Place\Infrastructure\Repository\Base\PlaceRepositoryInterface;
use App\Place\Infrastructure\Repository\PlaceARepository;
use App\Place\Infrastructure\Repository\PlaceHRepository;
use App\Place\Infrastructure\Repository\PlaceLRepository;
use App\Place\Infrastructure\Repository\PlacePRepository;
use App\Place\Infrastructure\Repository\PlaceRRepository;
use App\Place\Infrastructure\Repository\PlaceSRepository;
use App\Place\Infrastructure\Repository\PlaceTRepository;
use App\Place\Infrastructure\Repository\PlaceURepository;
use App\Place\Infrastructure\Repository\PlaceVRepository;
use App\Platform\Application\Utils\Constants\Code;
use App\Platform\Application\Utils\GPSConverter;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-05-31)
 * @package App\Command
 * @example bin/console app:coordinate:create [file]
 * @see http://download.geonames.org/export/dump/
 */
class AddPlaceCommand extends Command
{
    protected static $defaultName = 'app:place:add';

    public function __construct(
        protected EntityManagerInterface $manager,
        protected TranslatorInterface $translator,
        protected LocationDataService $locationDataService,
        protected PlaceARepository $placeARepository,
        protected PlaceHRepository $placeHRepository,
        protected PlaceLRepository $placeLRepository,
        protected PlacePRepository $placePRepository,
        protected PlaceRRepository $placeRRepository,
        protected PlaceSRepository $placeSRepository,
        protected PlaceTRepository $placeTRepository,
        protected PlaceURepository $placeURepository,
        protected PlaceVRepository $placeVRepository
    ) {
        parent::__construct();
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this
            ->setName(strval(self::$defaultName))
            ->setDescription('Creates coordinates from given file.')
            ->setDefinition([
                new InputArgument('feature-class', InputArgument::REQUIRED, 'The feature class'),
                new InputArgument('feature-code', InputArgument::REQUIRED, 'The feature code'),
                new InputArgument('google-link', InputArgument::REQUIRED, 'The google link'),
                new InputArgument('name', InputArgument::OPTIONAL, 'The name', null),
            ])
            ->setHelp(
                <<<'EOT'

The <info>app:coordinate:create</info> command creates coordinates from given file.

EOT
            );
    }

    /**
     * Returns the place repository according to given feature class.
     *
     * @throws Exception
     */
    protected function getPlaceRepository(string $featureClass): PlaceRepositoryInterface
    {
        return match ($featureClass) {
            Code::FEATURE_CLASS_A => $this->placeARepository,
            Code::FEATURE_CLASS_H => $this->placeHRepository,
            Code::FEATURE_CLASS_L => $this->placeLRepository,
            Code::FEATURE_CLASS_P => $this->placePRepository,
            Code::FEATURE_CLASS_R => $this->placeRRepository,
            Code::FEATURE_CLASS_S => $this->placeSRepository,
            Code::FEATURE_CLASS_T => $this->placeTRepository,
            Code::FEATURE_CLASS_U => $this->placeURepository,
            Code::FEATURE_CLASS_V => $this->placeVRepository,
            default => throw new Exception(sprintf('Unexpected feature class "%s" (%s:%d).', $featureClass, __FILE__, __LINE__)),
        };
    }

    /**
     * Execute the commands.
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws NonUniqueResultException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /* Read parameter. */
        $featureClass = strval($input->getArgument('feature-class'));
        $featureCode = strval($input->getArgument('feature-code'));
        $googleLink = strval($input->getArgument('google-link'));
        $name = strval($input->getArgument('name'));

        $translationCode = sprintf('%s.%s', $featureClass, $featureCode);
        $translation = $this->translator->trans($translationCode, [], 'place');

        if ($translationCode === $translation) {
            $output->writeln(sprintf('Unable to find feature class "%s" with feature code "%s".', $featureClass, $featureCode));

            return Command::INVALID;
        }

        $name = !empty($name) ? $name : GPSConverter::parseNameFromGoogleLinkDirect($googleLink);
        $result = GPSConverter::parseLatitudeAndLongitudeFromGoogleLinkDirect($googleLink);

        if ($result === false || $name === false) {
            $output->writeln(sprintf('Unable to parse given google link "%s".', $googleLink));

            return Command::INVALID;
        }

        [$latitude, $longitude] = $result;

        $placeRepository = $this->getPlaceRepository($featureClass);

        $places = $placeRepository->findBy([
            'name' => $name,
        ]);

        if (count($places) > 0) {
            $output->writeln(sprintf('%d records already found:', count($places)));
            foreach ($places as $place) {
                $googleLink = GPSConverter::decimalDegree2GoogleLink($place->getLatitude(), $place->getLongitude());
                $output->writeln(
                    sprintf(
                        '- %s: (GeoNameId = %d, FClass = %s, FCode = %s, CCode = %s, Google = "%s")',
                        $place->getName(),
                        $place->getGeonameId(),
                        $place->getFeatureClass(),
                        $place->getFeatureCode(),
                        $place->getCountryCode(),
                        $googleLink
                    )
                );
            }
            $output->writeln('');
        }

        $place = count($places) > 0 ? $places[0] : null;
        $additionalQuestion = $place !== null ? ' (already exists)' : ' (new record)';

        $locationPlace = $this->locationDataService->getLocationPlace($latitude, $longitude);

        if ($locationPlace === null) {
            $output->writeln(sprintf('I can not find that place (%f, %f).".', $latitude, $longitude));

            return Command::INVALID;
        }

        $lowestId = 100_000_000;
        $geoNameId = $placeRepository->getHighestGeonameId();
        $geoNameId++;

        if ($geoNameId < $lowestId) {
            $geoNameId = $lowestId;
        }

        $point = new Point($latitude, $longitude);
        $place = PlaceLoaderService::getPlace($featureClass);

        $place->setGeonameId($geoNameId);
        $place->setName($name);
        $place->setAsciiName($name);
        $place->setAlternateNames($name);
        $place->setCoordinate($point);
        $place->setFeatureClass($featureClass);
        $place->setFeatureCode($featureCode);
        $place->setCountryCode($locationPlace->getCountryCode());
        $place->setCc2($locationPlace->getCc2());
        $place->setAdmin1Code($locationPlace->getAdmin1Code());
        $place->setAdmin2Code($locationPlace->getAdmin2Code());
        $place->setAdmin3Code($locationPlace->getAdmin3Code());
        $place->setAdmin4Code($locationPlace->getAdmin4Code());
        $place->setPopulation(0);
        $place->setElevation($locationPlace->getElevation());
        $place->setDem($locationPlace->getDem());
        $place->setTimezone($locationPlace->getTimezone());
        $place->setModificationDate(new DateTime());
        $place->setCreatedAt(new DateTimeImmutable());
        $place->setUpdatedAt(new DateTimeImmutable());

        $googleLink = GPSConverter::decimalDegree2GoogleLink($latitude, $longitude);

        $output->writeln(sprintf('Add new place%s', $additionalQuestion));
        $output->writeln(sprintf('Name:          %s', $place->getName()));
        $output->writeln(sprintf('Latitude:      %f', $place->getLatitude()));
        $output->writeln(sprintf('Longitude:     %f', $place->getLongitude()));
        $output->writeln(sprintf('Feature class: %s', $place->getFeatureClass()));
        $output->writeln(sprintf('Feature code:  %s', $place->getFeatureCode()));
        $output->writeln(sprintf('Translation:   %s', $this->translator->trans(sprintf('%s.%s', $place->getFeatureClass(), $place->getFeatureCode()), [], 'place')));
        $output->writeln(sprintf('Country code:  %s', $place->getCountryCode()));
        $output->writeln(sprintf('Google link:   %s', $googleLink));
        $output->writeln(sprintf('Location:      %s', $locationPlace->getName(true)));
        $output->writeln('');

        $question = 'Add this place? [y|n] ';

        $helper = $this->getHelper('question');

        if (!$helper instanceof QuestionHelper) {
            throw new Exception(sprintf('Unable to get question (%s:%d).', __FILE__, __LINE__));
        }

        $question = new ConfirmationQuestion($question, false);

        /* Cancel task (answer "no" given). */
        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        $this->manager->persist($place);
        $this->manager->flush();

        $output->writeln('');
        $output->writeln(sprintf('"%s" written.', $name));

        /* Command successfully executed. */
        return Command::SUCCESS;
    }
}
