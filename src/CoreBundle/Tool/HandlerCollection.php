<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use InvalidArgumentException;

use function sprintf;

/**
 * Class HandlerCollection
 *
 * @package App\CoreBundle\Tool
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class HandlerCollection
{
    private iterable $handlers;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function getHandler(string $title): AbstractTool
    {
        foreach ($this->handlers as $handler) {
            if ($title === $handler->getTitle()) {
                return $handler;
            }
        }

        throw new InvalidArgumentException(sprintf('Cannot handle tool "%s"', $title));
    }

    /**
     * @return iterable
     */
    public function getCollection()
    {
        return $this->handlers;
    }
}
