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

namespace App\Platform\Application\Utils\Validator;

use App\Media\Infrastructure\Container\File;
use App\Media\Infrastructure\Container\Json;
use App\Platform\Application\Utils\Checker\CheckerClass;
use App\Platform\Application\Utils\Constants\Constants;
use App\Platform\Transport\Exception\FileNotFoundException;
use Exception;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Resolvers\SchemaResolver;
use Opis\JsonSchema\Validator as OpisJsonSchemaValidator;
use stdClass;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 */
class Validator
{
    protected const PATH_SCHEMA_DRAFT_07 = 'data/json/schema/draft-07.json';

    protected const ID_SCHEMA_GENERAL = 'http://api.example.tld/schema.json';

    protected const CONST_MAX_ERRORS = 9999;
    protected ?ValidationError $lastErrors = null;

    protected bool $isValidated = false;

    public function __construct(
        protected File|Json $data,
        protected File|Json $schema,
        protected ?string $pathRoot = null
    ) {
    }

    /**
     * Validates the given JSON files.
     *
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function validate(): bool
    {
        $this->isValidated = true;

        $validator = new OpisJsonSchemaValidator();
        $validator->setMaxErrors(self::CONST_MAX_ERRORS);

        $resolver = $validator->resolver();

        if (!$resolver instanceof SchemaResolver) {
            throw new Exception(sprintf('Unable to get SchemaResolver (%s:%d).', __FILE__, __LINE__));
        }

        match (true) {
            $this->schema instanceof File => $resolver->registerFile(self::ID_SCHEMA_GENERAL, $this->schema->getRealPath()),
            $this->schema instanceof Json => $resolver->registerRaw($this->schema->getJsonStringFormatted(), self::ID_SCHEMA_GENERAL)
        };

        $resolver->registerFile(Constants::JSON_SCHEMA_DRAFT_07, (new File(self::PATH_SCHEMA_DRAFT_07, $this->pathRoot))->getRealPath());

        $data = match (true) {
            $this->data instanceof File => $this->getJsonDecoded($this->data->getContentAsJson()->getJsonStringFormatted()),
            $this->data instanceof Json => $this->getJsonDecoded($this->data->getJsonStringFormatted())
        };

        $result = $validator->validate($data, self::ID_SCHEMA_GENERAL);

        if ($result->isValid()) {
            return true;
        }

        $this->lastErrors = $result->error();

        return false;
    }

    /**
     * Get last errors as array.
     *
     * @return array<int|string, mixed>
     */
    public function getLastErrorsArray(): array
    {
        if ($this->lastErrors === null) {
            return [];
        }

        $formatter = new ErrorFormatter();

        return $formatter->format($this->lastErrors, false);
    }

    /**
     * Get last errors as string representation.
     */
    public function getLastErrorsString(): string
    {
        $errors = [];

        foreach ($this->getLastErrorsArray() as $name => $value) {
            $errors[] = sprintf('%-12s %s', strval($name) . ':', strval($value));
        }

        return "\n\n" . implode("\n", $errors) . "\n";
    }

    /**
     * Get last errors as json.
     *
     * @throws Exception
     */
    public function getLastErrorsJson(): string
    {
        return (new Json($this->getLastErrorsArray()))->getJsonStringFormatted();
    }

    /**
     * Returns the status of validation as array.
     *
     * @return array<string, mixed>
     * @throws Exception
     */
    public function getStatusArray(): array
    {
        if (!$this->isValidated) {
            throw new Exception(sprintf('Please execute the validate method before (%s:%d).', __FILE__, __LINE__));
        }

        if ($this->lastErrors === null) {
            return [
                'valid' => true,
                'message' => 'The supplied JSON validates against the schema.',
            ];
        }

        return [
            'valid' => false,
            'message' => 'The supplied JSON does not validate against the schema.',
            'error' => $this->getLastErrorsArray(),
        ];
    }

    /**
     * Returns the status of validation as json.
     *
     * @throws Exception
     */
    public function getStatusJson(): string
    {
        return (new Json($this->getStatusArray()))->getJsonStringFormatted();
    }

    /**
     * Returns an array of given path.
     *
     * @throws Exception
     */
    protected function getJsonDecoded(string $json): stdClass
    {
        $object = (object)json_decode($json, null, 512, JSON_THROW_ON_ERROR);

        return (new CheckerClass($object))->checkStdClass();
    }
}
