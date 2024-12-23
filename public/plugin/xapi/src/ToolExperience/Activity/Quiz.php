<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\XApi\ToolExperience\Activity;

use App\Quiz\Domain\Entity\CQuiz;
use Xabbuh\XApi\Model\Activity;
use Xabbuh\XApi\Model\Definition;
use Xabbuh\XApi\Model\IRI;
use Xabbuh\XApi\Model\LanguageMap;

/**
 * Class Quiz.
 */
class Quiz extends BaseActivity
{
    /**
     * @var CQuiz
     */
    private $quiz;

    public function __construct(CQuiz $quiz)
    {
        $this->quiz = $quiz;
    }

    public function generate(): Activity
    {
        $langIso = api_get_language_isocode();

        $iri = $this->generateIri(
            WEB_CODE_PATH,
            'exercise/overview.php',
            ['exerciseId' => $this->quiz->getId()]
        );

        $definitionDescription = null;

        if ($this->quiz->getDescription()) {
            $definitionDescription = LanguageMap::create(
                [$langIso => $this->quiz->getDescription()]
            );
        }

        return new Activity(
            IRI::fromString($iri),
            new Definition(
                LanguageMap::create([$langIso => $this->quiz->getTitle()]),
                $definitionDescription,
                IRI::fromString('http://adlnet.gov/expapi/activities/assessment')
            )
        );
    }
}
