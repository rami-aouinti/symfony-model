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

use function sprintf;

/**
 * @package App\LtiBundle\Component
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class OutcomeReplaceRequest extends OutcomeRequest
{
    public function __construct(SimpleXMLElement $xml)
    {
        parent::__construct($xml);

        $this->responseType = OutcomeResponse::TYPE_REPLACE;
        $this->xmlRequest = $this->xmlRequest->replaceResultRequest;
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
        $resultScore = (string)$resultRecord->result->resultScore->textString;

        if (!is_numeric($resultScore)) {
            $this->statusInfo
                ->setSeverity(OutcomeResponseStatus::SEVERITY_ERROR)
                ->setCodeMajor(OutcomeResponseStatus::CODEMAJOR_FAILURE)
            ;

            return;
        }

        $resultScore = (float)$resultScore;

        if ($resultScore < 0 || $resultScore > 1) {
            $this->statusInfo
                ->setSeverity(OutcomeResponseStatus::SEVERITY_WARNING)
                ->setCodeMajor(OutcomeResponseStatus::CODEMAJOR_FAILURE)
            ;

            return;
        }

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

        $score = $evaluation->getMax() * $resultScore;

        $results = Result::load(null, $user->getId(), $evaluation->getId());

        if (empty($results)) {
            $result = new Result();
            $result->set_evaluation_id($evaluation->getId());
            $result->set_user_id($user->getId());
            $result->set_score($score);
            $result->add();
        } else {
            /** @var Result $result */
            $result = $results[0];
            $result->addResultLog($user->getId(), $evaluation->getId());
            $result->set_score($score);
            $result->save();
        }

        $this->statusInfo
            ->setSeverity(OutcomeResponseStatus::SEVERITY_STATUS)
            ->setCodeMajor(OutcomeResponseStatus::CODEMAJOR_SUCCESS)
            ->setDescription(
                sprintf(
                    $this->translator->trans('Score for user %d is %s'),
                    $user->getId(),
                    $resultScore
                )
            )
        ;
    }
}
