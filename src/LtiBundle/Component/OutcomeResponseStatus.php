<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\LtiBundle\Component;

/**
 * @package App\LtiBundle\Component
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class OutcomeResponseStatus
{
    public const string SEVERITY_STATUS = 'status';
    public const string SEVERITY_WARNING = 'warning';
    public const string SEVERITY_ERROR = 'error';

    public const string CODEMAJOR_SUCCESS = 'success';
    public const string CODEMAJOR_PROCESSING = 'processing';
    public const string CODEMAJOR_FAILURE = 'failure';
    public const string CODEMAJOR_UNSUPPORTED = 'unsupported';

    private string $codeMajor = '';

    private string $severity = '';

    private string $messageRefIdentifier = '';

    private string $operationRefIdentifier = '';

    private string $description = '';

    /**
     * Get codeMajor.
     */
    public function getCodeMajor(): string
    {
        return $this->codeMajor;
    }

    /**
     * Set codeMajor.
     *
     * @return OutcomeResponseStatus
     */
    public function setCodeMajor(string $codeMajor): static
    {
        $this->codeMajor = $codeMajor;

        return $this;
    }

    /**
     * Get severity.
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    /**
     * Set severity.
     *
     * @return OutcomeResponseStatus
     */
    public function setSeverity(string $severity): static
    {
        $this->severity = $severity;

        return $this;
    }

    /**
     * Get messageRefIdentifier.
     */
    public function getMessageRefIdentifier(): int|string
    {
        return $this->messageRefIdentifier;
    }

    /**
     * Set messageRefIdentifier.
     *
     * @return OutcomeResponseStatus
     */
    public function setMessageRefIdentifier(int $messageRefIdentifier): static
    {
        $this->messageRefIdentifier = $messageRefIdentifier;

        return $this;
    }

    /**
     * Get operationRefIdentifier.
     */
    public function getOperationRefIdentifier(): int|string
    {
        return $this->operationRefIdentifier;
    }

    /**
     * Set operationRefIdentifier.
     *
     * @return OutcomeResponseStatus
     */
    public function setOperationRefIdentifier(int $operationRefIdentifier): static
    {
        $this->operationRefIdentifier = $operationRefIdentifier;

        return $this;
    }

    /**
     * Get description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @return OutcomeResponseStatus
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
