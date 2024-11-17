<?php

declare(strict_types=1);

namespace App\User\Transport\Message;

use App\Platform\Application\Dto\FeedbackDto;

/**
 * Class SendFeedback
 *
 * @package App\User\Transport\Message
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final readonly class SendFeedback
{
    public function __construct(private FeedbackDto $feedback)
    {
    }

    public function getFeedback(): FeedbackDto
    {
        return $this->feedback;
    }
}
