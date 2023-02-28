<?php
declare(strict_types=1);

/**
 * Courses Student Entity
 *
 * These are here so I can test the Traits.
 */

namespace Fr3nch13\Utilities\Model\Entity;

/**
 * Courses Student Entity
 *
 * @property int $id
 * @property int $course_id
 * @property int $student_id
 * @property int $grade
 * @property \Fr3nch13\Utilities\Model\Entity\Course[] $courses
 * @property \Fr3nch13\Utilities\Model\Entity\Student[] $students
 */
final class CoursesStudent extends \Cake\ORM\Entity
{
}
