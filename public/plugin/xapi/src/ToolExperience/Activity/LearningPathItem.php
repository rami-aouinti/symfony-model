<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\XApi\ToolExperience\Activity;

use App\CourseBundle\Entity\CLp\CLpItem;
use Xabbuh\XApi\Model\Activity;
use Xabbuh\XApi\Model\Definition;
use Xabbuh\XApi\Model\IRI;
use Xabbuh\XApi\Model\LanguageMap;

/**
 * Class LearningPathItem.
 */
class LearningPathItem extends BaseActivity
{
    /**
     * @var CLpItem
     */
    private $lpItem;

    public function __construct(CLpItem $lpItem)
    {
        $this->lpItem = $lpItem;
    }

    public function generate(): Activity
    {
        $langIso = api_get_language_isocode();

        $iri = $this->generateIri(
            WEB_CODE_PATH,
            'lp/lp_controller.php',
            [
                'action' => 'view',
                'lp_id' => $this->lpItem->getLpId(),
                'isStudentView' => 'true',
                'lp_item' => $this->lpItem->getId(),
            ]
        );

        return new Activity(
            IRI::fromString($iri),
            new Definition(
                LanguageMap::create([$langIso => $this->lpItem->getTitle()]),
                null,
                IRI::fromString('http://id.tincanapi.com/activitytype/resource')
            )
        );
    }
}
