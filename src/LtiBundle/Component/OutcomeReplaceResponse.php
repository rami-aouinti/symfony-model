<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\LtiBundle\Component;

use SimpleXMLElement;

/**
 * @package App\LtiBundle\Component
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class OutcomeReplaceResponse extends OutcomeResponse
{
    /**
     * @param mixed|null $bodyParam
     */
    public function __construct(OutcomeResponseStatus $statusInfo, $bodyParam = null)
    {
        $statusInfo->setOperationRefIdentifier('replaceResult');

        parent::__construct($statusInfo, $bodyParam);
    }

    protected function generateBody(SimpleXMLElement $xmlBody): void
    {
        $xmlBody->addChild('replaceResultResponse');
    }
}
