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

use App\Calendar\Application\Utils\Db\Entity;
use App\Calendar\Application\Utils\Db\Repository;
use App\Media\Infrastructure\Container\Json;
use App\Platform\Application\Utils\Converter\NamingConventions;
use App\Platform\Transport\Exception\FileNotFoundException;
use App\Platform\Transport\Exception\FileNotReadableException;
use App\Platform\Transport\Exception\FunctionJsonEncodeException;
use App\Platform\Transport\Exception\FunctionReplaceException;
use App\Platform\Transport\Exception\OptionInvalidException;
use App\Platform\Transport\Exception\TypeInvalidException;
use Exception;
use JsonException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract class BaseCommand
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 */
abstract class BaseCommand extends Command
{
    protected const string NAME_OPTION_FORMAT = 'format';

    protected const string NAME_OPTION_FORMAT_SHORT = 'f';

    protected const string OPTION_FORMAT_TEXT = 'text';

    protected const string OPTION_FORMAT_JSON = 'json';

    protected const string TEMPLATE_PRINT = '%-40s %s';

    protected const string MESSAGE_SUCCESS = 'The result is delivered in the data part.';

    protected InputInterface $input;

    protected OutputInterface $output;

    public function __construct(
        protected Entity $entity,
        protected Repository $repository
    ) {
        parent::__construct();
    }

    /**
     * Configures the command (default).
     */
    protected function configure(): void
    {
        $this
            ->addOption(self::NAME_OPTION_FORMAT, self::NAME_OPTION_FORMAT_SHORT, InputOption::VALUE_REQUIRED, 'Output format.', self::OPTION_FORMAT_JSON);

        $this->configureCommand();
    }

    /**
     * Configures the command (single command).
     */
    abstract protected function configureCommand(): void;

    /**
     * Gets the value.
     */
    protected function getValue(mixed $value): string
    {
        return match (true) {
            is_string($value) => $value,
            is_bool($value) => $value ? 'Yes' : 'No',
            default => strval($value),
        };
    }

    /**
     * Gets the key.
     */
    protected function getKey(mixed $key, ?string $recursiveName): string
    {
        if (is_numeric($key)) {
            $key = sprintf('%d:', $key);
        }

        if ($recursiveName === null) {
            return strval($key);
        }

        return sprintf('%s_%s', $recursiveName, strval($key));
    }

    /**
     * Prints the version array as text.
     *
     * @param array<int|string, mixed> $outputArray
     * @throws FunctionReplaceException
     */
    protected function printText(OutputInterface $output, array $outputArray, ?string $recursiveName = null): void
    {
        if ($recursiveName === null) {
            $output->writeln('');
        }

        foreach ($outputArray as $key => $value) {
            $newKey = $this->getKey($key, $recursiveName);

            if (is_array($value)) {
                if ($recursiveName !== null) {
                    $output->writeln('');
                }
                $this->printText($output, $value, $newKey);

                continue;
            }

            $title = (new NamingConventions($newKey))->getTitle();
            $output->writeln(sprintf(self::TEMPLATE_PRINT, sprintf('  %s:', $title), $this->getValue($value)));
        }

        if ($recursiveName === null) {
            $output->writeln('');
        }
    }

    /**
     * Prints the version array as json.
     *
     * @param array<int|string, mixed> $array
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws TypeInvalidException
     * @throws FunctionJsonEncodeException
     * @throws JsonException
     */
    protected function printJson(OutputInterface $output, array $array): void
    {
        $output->writeln((new Json($array))->getJsonStringFormatted());
    }

    /**
     * @param array<int|string, mixed> $data
     * @param array<int|string, mixed>|null $extra
     * @return array<int|string, mixed>
     */
    protected function getSuccessArray(array $data, array $extra = null): array
    {
        $return = [
            'valid' => true,
            'message' => self::MESSAGE_SUCCESS,
        ];

        if ($extra !== null) {
            $return = array_merge($return, $extra);
        }

        $return['data'] = $data;

        return $return;
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function getEmptyArray(string $message): array
    {
        return [
            'valid' => false,
            'message' => $message,
            'data' => [],
        ];
    }

    /**
     * Execute the command (default).
     *
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws OptionInvalidException
     * @throws TypeInvalidException
     * @throws FunctionJsonEncodeException
     * @throws JsonException
     * @throws FunctionReplaceException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $format = strval($this->input->getOption(self::NAME_OPTION_FORMAT));

        match ($format) {
            self::OPTION_FORMAT_TEXT => $this->printText($output, $this->executeCommand()),
            self::OPTION_FORMAT_JSON => $this->printJson($output, $this->executeCommand()),
            default => throw new OptionInvalidException($format, [self::OPTION_FORMAT_TEXT, self::OPTION_FORMAT_JSON]),
        };

        return Command::SUCCESS;
    }

    /**
     * Execute the command (single command).
     *
     * @return array<int|string, mixed>
     * @throws Exception
     */
    abstract protected function executeCommand(): array;
}
