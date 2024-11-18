<?php

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Component\CourseCopy\Resources;

/**
 * Class GradeBookBackup.
 */
class GradeBookBackup extends Resource
{
    public $categories;

    /**
     * @param array $categories
     */
    public function __construct($categories)
    {
        parent::__construct(uniqid(), RESOURCE_GRADEBOOK);
        $this->categories = $categories;
    }

    /**
     * @return string
     */
    public function show()
    {
        parent::show();
        echo get_lang('All');
    }
}
