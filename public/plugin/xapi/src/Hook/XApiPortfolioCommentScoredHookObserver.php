<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

use App\CoreBundle\Entity\PortfolioComment;
use Chamilo\PluginBundle\XApi\ToolExperience\Statement\PortfolioCommentScored;

class XApiPortfolioCommentScoredHookObserver extends XApiActivityHookObserver implements HookPortfolioCommentScoredObserverInterface
{
    public function hookCommentScored(HookPortfolioCommentScoredEventInterface $hookEvent): void
    {
        /** @var PortfolioComment $comment */
        $comment = $hookEvent->getEventData()['comment'];

        $statement = (new PortfolioCommentScored($comment))->generate();

        $this->saveSharedStatement($statement);
    }
}
