<?php

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Component\CourseCopy\Resources;

/**
 * Class CourseCopyTestCategory.
 *
 * @author Hubert Borderiou <hubert.borderiou@grenet.fr>
 */
class CourseCopyTestCategory extends Resource
{
    /**
     * The title.
     */
    public $title;

    /**
     * The description.
     */
    public $description;

    /**
     * Create a new TestCategory.
     *
     * @param int    $id
     * @param string $title
     * @param string $description
     */
    public function __construct($id, $title, $description)
    {
        parent::__construct($id, RESOURCE_TEST_CATEGORY);
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * Show the test_category title, used in the partial recycle_course.php form.
     */
    public function show()
    {
        parent::show();
        echo $this->title;
    }
}
