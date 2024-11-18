<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\LtiBundle\Component;

use Doctrine\ORM\EntityManager;
use SimpleXMLElement;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @package App\LtiBundle\Component
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
abstract class OutcomeRequest
{
    protected string $responseType;

    protected SimpleXMLElement $xmlHeaderInfo;

    protected SimpleXMLElement $xmlRequest;

    protected OutcomeResponseStatus $statusInfo;

    protected array $responseBodyParam;

    protected EntityManager $entityManager;
    protected TranslatorInterface $translator;

    public function __construct(SimpleXMLElement $xml)
    {
        $this->statusInfo = new OutcomeResponseStatus();

        $this->xmlHeaderInfo = $xml->imsx_POXHeader->imsx_POXRequestHeaderInfo;
        $this->xmlRequest = $xml->imsx_POXBody->children();
    }

    public function setEntityManager(EntityManager $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function process(): OutcomeReplaceResponse|OutcomeDeleteResponse|OutcomeResponse|OutcomeUnsupportedResponse|OutcomeReadResponse|null
    {
        $this->processHeader();
        $this->processBody();

        return match ($this->responseType) {
            OutcomeResponse::TYPE_REPLACE => new OutcomeReplaceResponse($this->statusInfo, $this->responseBodyParam),
            OutcomeResponse::TYPE_READ => new OutcomeReadResponse($this->statusInfo, $this->responseBodyParam),
            OutcomeResponse::TYPE_DELETE => new OutcomeDeleteResponse($this->statusInfo, $this->responseBodyParam),
            default => new OutcomeUnsupportedResponse($this->statusInfo, $this->responseBodyParam),
        };
    }

    protected function processHeader(): void
    {
        $info = $this->xmlHeaderInfo;

        $this->statusInfo->setMessageRefIdentifier($info->imsx_messageIdentifier);

        error_log("Service Request: tool version {$info->imsx_version} message ID {$info->imsx_messageIdentifier}");
    }

    abstract protected function processBody();
}
