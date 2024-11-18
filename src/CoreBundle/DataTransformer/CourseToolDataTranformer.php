<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\CoreBundle\ApiResource\CourseTool;
use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Tool\AbstractTool;
use App\CoreBundle\Tool\ToolChain;
use App\CoreBundle\Traits\CourseFromRequestTrait;
use App\CourseBundle\Entity\CTool;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CourseToolDataTranformer implements DataTransformerInterface
{
    use CourseFromRequestTrait;

    public function __construct(
        protected RequestStack $requestStack,
        protected EntityManagerInterface $entityManager,
        protected readonly ToolChain $toolChain,
    ) {
    }

    public function transform($object, string $to, array $context = []): object
    {
        \assert($object instanceof CTool);

        $tool = $object->getTool();

        $toolModel = $this->toolChain->getToolFromName(
            $tool->getTitle()
        );

        $course = $this->getCourse();

        $cTool = new CourseTool();
        $cTool->iid = $object->getIid();
        $cTool->title = $object->getTitle();
        $cTool->visibility = $object->getVisibility();
        $cTool->resourceNode = $object->resourceNode;
        $cTool->illustrationUrl = $object->illustrationUrl;
        $cTool->url = $this->generateToolUrl($toolModel, $course);
        $cTool->tool = $toolModel;

        return $cTool;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $data instanceof CTool && $to === CourseTool::class;
    }

    private function generateToolUrl(AbstractTool $tool, Course $course): string
    {
        $link = $tool->getLink();

        if (strpos($link, 'nodeId')) {
            $nodeId = (string)$course->getResourceNode()->getId();
            $link = str_replace(':nodeId', $nodeId, $link);
        }

        return $link . '?'
            . http_build_query([
                'cid' => $this->getCourse()->getId(),
                'sid' => $this->getSession()?->getId(),
                'gid' => 0,
            ]);
    }
}
