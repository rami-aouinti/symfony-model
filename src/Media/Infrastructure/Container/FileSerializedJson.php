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

namespace App\Media\Infrastructure\Container;

use App\Platform\Transport\Exception\FileNotFoundException;
use App\Platform\Transport\Exception\FileNotReadableException;
use App\Platform\Transport\Exception\FunctionJsonEncodeException;
use App\Platform\Transport\Exception\TypeInvalidException;
use JsonException;

use function App\Calendar\Infrastructure\Container\gettype;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 */
class FileSerializedJson extends FileSerialized
{
    public function __construct(string $path)
    {
        parent::__construct($path);
    }

    /**
     * Returns the Json representation of this file.
     *
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws TypeInvalidException
     * @throws FunctionJsonEncodeException
     * @throws JsonException
     */
    public function getJson(): Json
    {
        $json = null;

        if ($this->checkSerializedJson()) {
            $json = $this->getUnserialized();
        }

        if (!$json instanceof Json) {
            throw new TypeInvalidException('Json', gettype($json));
        }

        return $json;
    }

    /**
     * Checks the serialized json file.
     *
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws TypeInvalidException
     * @throws FunctionJsonEncodeException
     * @throws JsonException
     */
    protected function checkSerializedJson(): bool
    {
        $fileSerialized = new File($this->pathSerialized);

        if (!$fileSerialized->exist()) {
            $this->saveSerializedJson($fileSerialized);
        }

        return true;
    }

    /**
     * Writes serialized json to file.
     *
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws TypeInvalidException
     * @throws FunctionJsonEncodeException
     * @throws JsonException
     */
    protected function saveSerializedJson(File $fileSerialized): bool
    {
        $content = $this->getContentAsText();

        $json = new Json($content);

        $fileSerialized->write(serialize($json));

        return true;
    }
}
