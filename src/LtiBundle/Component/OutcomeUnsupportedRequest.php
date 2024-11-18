<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\LtiBundle\Component;

use SimpleXMLElement;

/**
 * @package App\LtiBundle\Component
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class OutcomeUnsupportedRequest extends OutcomeRequest
{
    public function __construct(SimpleXMLElement $xml, string $name)
    {
        parent::__construct($xml);

        $this->responseType = $name;
    }

    protected function processBody(): void
    {
        $this->statusInfo
            ->setSeverity(OutcomeResponseStatus::SEVERITY_STATUS)
            ->setCodeMajor(OutcomeResponseStatus::CODEMAJOR_UNSUPPORTED)
            ->setDescription(
                $this->responseType . ' is not supported'
            )
        ;
    }
}
