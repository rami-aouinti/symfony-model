<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\XApi\ToolExperience\Activity;

use App\CoreBundle\Entity\PortfolioComment as PortfolioCommentEntity;
use Xabbuh\XApi\Model\Activity;
use Xabbuh\XApi\Model\Definition;
use Xabbuh\XApi\Model\IRI;

/**
 * Class PortfolioComment.
 */
class PortfolioComment extends BaseActivity
{
    /**
     * @var PortfolioCommentEntity
     */
    private $comment;

    public function __construct(PortfolioCommentEntity $comment)
    {
        $this->comment = $comment;
    }

    public function generate(): Activity
    {
        $iri = $this->generateIri(
            WEB_CODE_PATH,
            'portfolio/index.php',
            [
                'action' => 'view',
                'id' => $this->comment->getItem()->getId(),
                'comment' => $this->comment->getId(),
            ]
        );

        return new Activity(
            IRI::fromString($iri),
            new Definition(
                null,
                null,
                IRI::fromString('http://activitystrea.ms/schema/1.0/comment')
            )
        );
    }
}
