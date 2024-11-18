<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller\Api;

use App\CourseBundle\Entity\CLinkCategory;
use App\CourseBundle\Repository\CLinkCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateVisibilityLinkCategory extends AbstractController
{
    public function __invoke(CLinkCategory $linkCategory, CLinkCategoryRepository $repo): CLinkCategory
    {
        $repo->toggleVisibilityPublishedDraft($linkCategory);
        $linkCategory->toggleVisibility();

        return $linkCategory;
    }
}
