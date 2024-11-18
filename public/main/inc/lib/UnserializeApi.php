<?php

/* For licensing terms, see /license.txt */

class UnserializeApi
{
    /**
     * @param string $type
     * @param string $serialized
     *
     * @return mixed
     */
    public static function unserialize($type, $serialized, $ignoreErrors = false)
    {
        $allowedClasses = [];

        switch ($type) {
            case 'career':
            case 'sequence_graph':
                $allowedClasses = [
                    \Fhaculty\Graph\Graph::class,
                    \Fhaculty\Graph\Set\VerticesMap::class,
                    \Fhaculty\Graph\Set\Vertices::class,
                    \Fhaculty\Graph\Set\Edges::class,
                    \Fhaculty\Graph\Vertex::class,
                    \Fhaculty\Graph\Edge\Base::class,
                    \Fhaculty\Graph\Edge\Directed::class,
                    \Fhaculty\Graph\Edge\Undirected::class,
                ];
                break;
            case 'course':
                $allowedClasses = [
                    \App\CourseBundle\Component\CourseCopy\Course::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\Announcement::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\Asset::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\Attendance::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\CalendarEvent::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\CourseCopyLearnpath::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\CourseCopyTestCategory::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\CourseDescription::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\CourseSession::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\Document::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\Forum::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\ForumCategory::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\ForumPost::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\ForumTopic::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\Glossary::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\GradeBookBackup::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\LearnPathCategory::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\Link::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\LinkCategory::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\Quiz::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\QuizQuestion::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\QuizQuestionOption::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\ScormDocument::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\Survey::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\SurveyInvitation::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\SurveyQuestion::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\Thematic::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\ToolIntro::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\Wiki::class,
                    \App\CourseBundle\Component\CourseCopy\Resources\Work::class,
                    \App\CourseBundle\Entity\CLp\CLpCategory::class,
                    stdClass::class,
                    Category::class,
                    AttendanceLink::class,
                    DropboxLink::class,
                    Evaluation::class,
                    ExerciseLink::class,
                    ForumThreadLink::class,
                    LearnpathLink::class,
                    LinkFactory::class,
                    Result::class,
                    StudentPublicationLink::class,
                    SurveyLink::class,
                ];
            // no break
            case 'lp':
                $allowedClasses = array_merge(
                    $allowedClasses,
                    [
                        learnpath::class,
                        learnpathItem::class,
                        aicc::class,
                        aiccBlock::class,
                        aiccItem::class,
                        aiccObjective::class,
                        aiccResource::class,
                        scorm::class,
                        scormItem::class,
                        scormMetadata::class,
                        scormOrganization::class,
                        scormResource::class,
                        Link::class,
                    ]
                );
                break;
            case 'not_allowed_classes':
            default:
                $allowedClasses = false;
        }

        if ($ignoreErrors) {
            return @unserialize(
                $serialized,
                ['allowed_classes' => $allowedClasses]
            );
        }

        return @unserialize(
            $serialized,
            ['allowed_classes' => $allowedClasses]
        );
    }
}
