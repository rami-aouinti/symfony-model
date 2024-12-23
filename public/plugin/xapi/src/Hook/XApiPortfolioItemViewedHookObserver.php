<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

use App\CoreBundle\Entity\Portfolio;
use Chamilo\PluginBundle\XApi\ToolExperience\Statement\PortfolioItemViewed;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class XApiPortfolioItemViewedHookObserver extends XApiActivityHookObserver implements HookPortfolioItemViewedObserverInterface
{
    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function hookItemViewed(HookPortfolioItemViewedEventInterface $hookEvent): void
    {
        /** @var Portfolio $item */
        $item = $hookEvent->getEventData()['portfolio'];

        $statement = (new PortfolioItemViewed($item))->generate();

        $this->saveSharedStatement($statement);
    }
}
