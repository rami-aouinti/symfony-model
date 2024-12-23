<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\XApi\ToolExperience\Statement;

use App\CoreBundle\Entity\Portfolio as PortfolioEntity;
use App\CoreBundle\Entity\PortfolioComment as PortfolioCommentEntity;

abstract class PortfolioComment extends BaseStatement
{
    /**
     * @var PortfolioCommentEntity
     */
    protected $comment;

    /**
     * @var PortfolioCommentEntity|null
     */
    protected $parentComment;

    /**
     * @var PortfolioEntity
     */
    protected $item;

    public function __construct(PortfolioCommentEntity $comment)
    {
        $this->comment = $comment;
        $this->item = $this->comment->getItem();
        $this->parentComment = $this->comment->getParent();
    }
}
