<?php

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Component\CourseCopy\Resources;

/**
 * Attendance backup script.
 */
class Attendance extends Resource
{
    public $params = [];
    public $attendance_calendar = [];

    /**
     * Create a new Thematic.
     *
     * @param array $params
     */
    public function __construct($params)
    {
        parent::__construct($params['id'], RESOURCE_ATTENDANCE);
        $this->params = $params;
    }

    public function show()
    {
        parent::show();
        echo $this->params['name'];
    }

    public function add_attendance_calendar($data)
    {
        $this->attendance_calendar[] = $data;
    }
}
