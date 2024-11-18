<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\LtiBundle\Component;

use App\CoreBundle\Entity\Gradebook\GradebookEvaluation;
use App\CoreBundle\Entity\User\User;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\TransactionRequiredException;
use Result;
use SimpleXMLElement;

/**
 * @package App\LtiBundle\Component
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class OutcomeDeleteRequest extends OutcomeRequest
{
    public function __construct(SimpleXMLElement $xml)
    {
        parent::__construct($xml);

        $this->responseType = OutcomeResponse::TYPE_DELETE;
        $this->xmlRequest = $this->xmlRequest->deleteResultRequest;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransactionRequiredException
     */
    protected function processBody(): void
    {
        $resultRecord = $this->xmlRequest->resultRecord;
        $sourcedId = (string)$resultRecord->sourcedGUID->sourcedId;
        $sourcedId = htmlspecialchars_decode($sourcedId);

        $sourcedParts = json_decode($sourcedId, true);

        if (empty($sourcedParts)) {
            $this->statusInfo
                ->setSeverity(OutcomeResponseStatus::SEVERITY_ERROR)
                ->setCodeMajor(OutcomeResponseStatus::CODEMAJOR_FAILURE)
            ;

            return;
        }

        /** @var GradebookEvaluation $evaluation */
        $evaluation = $this->entityManager->find(GradebookEvaluation::class, $sourcedParts['e']);

        /** @var User $user */
        $user = $this->entityManager->find(User::class, $sourcedParts['u']);

        if (empty($evaluation) || empty($user)) {
            $this->statusInfo
                ->setSeverity(OutcomeResponseStatus::SEVERITY_STATUS)
                ->setCodeMajor(OutcomeResponseStatus::CODEMAJOR_FAILURE)
            ;

            return;
        }

        $results = Result::load(null, $user->getId(), $evaluation->getId());

        if (empty($results)) {
            $this->statusInfo
                ->setSeverity(OutcomeResponseStatus::SEVERITY_STATUS)
                ->setCodeMajor(OutcomeResponseStatus::CODEMAJOR_FAILURE)
            ;

            return;
        }

        /** @var Result $result */
        $result = $results[0];
        $result->addResultLog($user->getId(), $evaluation->getId());
        $result->delete();

        $this->statusInfo
            ->setSeverity(OutcomeResponseStatus::SEVERITY_STATUS)
            ->setCodeMajor(OutcomeResponseStatus::CODEMAJOR_SUCCESS)
        ;
    }
}
