<?php

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Hook\Interfaces;

/**
 * Interface HookMyStudentsQuizTrackingEventInterface.
 */
interface HookMyStudentsQuizTrackingEventInterface extends HookEventInterface
{
    public function notifyTrackingHeader(): array;

    /**
     * @param int $quizId
     * @param int $studentId
     */
    public function notifyTrackingContent($quizId, $studentId): array;
}
