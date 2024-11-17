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

use App\Media\Infrastructure\Container\Base\BaseContainer;
use App\Platform\Transport\Exception\ClassNotFoundException;
use App\Platform\Transport\Exception\TypeInvalidException;
use CurlHandle;
use Stringable;

use function App\Calendar\Infrastructure\Container\gettype;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 */
class Curl extends BaseContainer implements Stringable
{
    public function __construct(
        protected string $url
    ) {
    }

    /**
     * Returns the url of this container.
     */
    public function __toString(): string
    {
        return $this->url;
    }

    /**
     * Returns the url of this container.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Sets the url of this container.
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Returns the file content as text.
     *
     * @throws ClassNotFoundException
     * @throws TypeInvalidException
     */
    public function getContentAsText(): string
    {
        $curlHandle = curl_init($this->url);

        if (!$curlHandle instanceof CurlHandle) {
            throw new ClassNotFoundException(CurlHandle::class);
        }

        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);

        $data = curl_exec($curlHandle);

        if (!is_string($data)) {
            throw new TypeInvalidException('string', gettype($data));
        }

        curl_close($curlHandle);

        return $data;
    }
}
