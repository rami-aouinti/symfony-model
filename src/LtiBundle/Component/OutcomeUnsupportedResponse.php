<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\LtiBundle\Component;

use SimpleXMLElement;

/**
 * @package App\LtiBundle\Component
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class OutcomeUnsupportedResponse extends OutcomeResponse
{
    /**
     * @param int $type
     */
    public function __construct(OutcomeResponseStatus $statusInfo, $type)
    {
        $statusInfo->setOperationRefIdentifier((int)$type);

        parent::__construct($statusInfo);
    }

    protected function generateBody(SimpleXMLElement $xmlBody): void
    {
    }
}
