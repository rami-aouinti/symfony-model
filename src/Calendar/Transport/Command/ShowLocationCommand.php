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

use App\Media\Application\Service\ImageDataService;
use App\Place\Application\Service\Entity\PlaceLoaderService;
use App\Place\Application\Service\LocationDataService;
use App\Platform\Application\Utils\Timer;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-04-29)
 * @package App\Command
 * @example bin/console app:image:show "path"
 * @see https://github.com/posva/catimg
 */
class ShowLocationCommand extends Command
{
    protected const string REGEXP_OUTPUT = '%%-%ds %%-%ds %%s';

    protected const string LINE_BREAK = "\n";
    protected static $defaultName = 'app:location:show';

    protected bool $debug = false;

    protected bool $verbose = false;

    public function __construct(
        protected PlaceLoaderService $placeLoaderService,
        protected LocationDataService $locationDataService
    ) {
        parent::__construct();
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this
            ->setName('app:location:show')
            ->setDescription('Shows location properties.')
            ->setDefinition([
                new InputArgument('latitude', InputArgument::REQUIRED, 'The latitude (y position).'),
                new InputArgument('longitude', InputArgument::REQUIRED, 'The longitude (x position).'),
                new InputOption('debug', 'd', InputOption::VALUE_NONE, 'Switch to debug mode.'),
            ])
            ->setHelp(
                <<<'EOT'

The <info>app:location:show</info> shows location properties:
  <info>php %command.full_name%</info>
EOT
            );
    }

    /**
     * Returns the output lines from location data.
     *
     * @param array<string, array<string, mixed>> $locationData
     * @return string[]
     */
    protected function getOutputLines(array $locationData): array
    {
        $outputLines = [];

        foreach ($locationData as $key => $data) {
            $valueFormatted = strval($data[ImageDataService::KEY_NAME_VALUE_FORMATTED]);

            $format = sprintf(self::REGEXP_OUTPUT, ImageDataService::WIDTH_TITLE, ImageDataService::WIDTH_TITLE);
            $outputLine = sprintf($format, strval($data[ImageDataService::KEY_NAME_TITLE]), $key, $valueFormatted);

            $outputLines[] = $outputLine;
        }

        return $outputLines;
    }

    /**
     * Returns the max length of given array.
     *
     * @param string[] $lines
     */
    protected function getMaxLength(array $lines): int
    {
        $outputMaxLength = 0;

        foreach ($lines as $line) {
            if (strlen($line) > $outputMaxLength) {
                $outputMaxLength = strlen($line);
            }
        }

        return $outputMaxLength;
    }

    /**
     * Prints image data.
     *
     * @throws DoctrineDBALException
     * @throws Exception
     */
    protected function printLocationData(float $latitude, float $longitude, OutputInterface $output): void
    {
        $timer = Timer::start();

        $locationData = $this->locationDataService->getLocationDataFull($latitude, $longitude);

        $outputLines = $this->getOutputLines($locationData);
        $outputMaxLength = $this->getMaxLength($outputLines);

        $format = sprintf(self::REGEXP_OUTPUT, LocationDataService::WIDTH_TITLE, LocationDataService::WIDTH_TITLE);

        $imageDataText = sprintf($format, 'Title', 'Key', 'Value') . self::LINE_BREAK;
        $imageDataText .= str_repeat('-', $outputMaxLength) . self::LINE_BREAK;
        foreach ($outputLines as $outputLine) {
            $imageDataText .= $outputLine . self::LINE_BREAK;
        }

        $time = Timer::stop($timer);

        $title = sprintf('Location properties (%.4fs)', $time);

        $output->writeln('');
        $output->writeln($title);
        $output->writeln(str_repeat('-', strlen($title)));
        $output->writeln('');
        $output->writeln($imageDataText);
        $output->writeln('');
        $output->writeln('');
    }

    /**
     * Execute the commands.
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->debug = boolval($input->getOption('debug'));
        $this->verbose = boolval($input->getOption('verbose'));

        $latitude = floatval(preg_replace('~^_~', '-', strval($input->getArgument('latitude'))));
        $longitude = floatval(preg_replace('~^_~', '-', strval($input->getArgument('longitude'))));

        $this->locationDataService->setDebug($this->debug);
        $this->locationDataService->setVerbose($this->verbose);

        /* Print image data. */
        $this->printLocationData($latitude, $longitude, $output);

        /* Command successfully executed. */
        return Command::SUCCESS;
    }
}
