<?php

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Component\CourseCopy\Resources;

/**
 * An Quiz
 * Exercises backup script.
 *
 * @author Bart Mollet <bart.mollet@hogent.be>
 */
class Quiz extends Resource
{
    public $obj; //question

    /**
     * @param int $obj
     */
    public function __construct($obj)
    {
        $this->obj = $obj;
        $this->obj->quiz_type = $this->obj->type;
        parent::__construct($obj->id, RESOURCE_QUIZ);
    }

    /**
     * Add a question to this Quiz.
     *
     * @param int $id
     * @param int $questionOrder
     */
    public function add_question($id, $questionOrder)
    {
        $this->obj->question_ids[] = $id;
        $this->obj->question_orders[] = $questionOrder;
    }

    /**
     * Show this question.
     */
    public function show()
    {
        parent::show();
        echo $this->obj->title;
    }
}
