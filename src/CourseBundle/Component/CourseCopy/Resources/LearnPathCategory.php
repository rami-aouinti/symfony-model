<?php

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Component\CourseCopy\Resources;

use App\CourseBundle\Entity\CLp\CLpCategory;

/**
 * Class LearnPathCategory.
 */
class LearnPathCategory extends Resource
{
    /**
     * @var CLpCategory
     */
    public $object;

    /**
     * @param int    $id
     * @param string $object
     */
    public function __construct($id, $object)
    {
        parent::__construct($id, RESOURCE_LEARNPATH_CATEGORY);
        $this->object = $object;
    }

    /**
     * Show this resource.
     */
    public function show()
    {
        parent::show();
        echo $this->object->getTitle();
    }
}
