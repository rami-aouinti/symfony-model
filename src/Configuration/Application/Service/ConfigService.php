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

namespace App\Configuration\Application\Service;

use App\Platform\Transport\Exception\ConfigurationNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-02-22)
 * @package App\Service
 */
class ConfigService
{
    final public const string PARAMETER_NAME_BACKEND_TITLE_MAIN = 'backend.title.main';

    final public const string PARAMETER_NAME_BACKEND_TITLE_LOGIN = 'backend.title.login';

    public function __construct(
        protected ParameterBagInterface $parameterBag
    ) {
    }

    /**
     * Get config from parameter bag.
     */
    public function getConfig(string $name): string
    {
        if (!$this->parameterBag->has($name)) {
            throw new ConfigurationNotFoundException($name);
        }

        return strval($this->parameterBag->get($name));
    }
}
