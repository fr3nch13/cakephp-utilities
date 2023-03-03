<?php
declare(strict_types=1);

/**
 * Courses Students Model
 *
 * Used to test traits within a context for Student
 */

namespace Fr3nch13\Utilities\Model\Table;

/**
 * Courses Students Model
 *
 * Used for testing the traits and behaviors.
 *
 * @property \Fr3nch13\Utilities\Model\Table\CoursesTable $Courses
 * @property \Fr3nch13\Utilities\Model\Table\StudentsTable $Students
 */
final class CoursesStudentsTable extends \Cake\ORM\Table
{
    /**
     * Initialize method
     *
     * @param array<mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config = []): void
    {
        parent::initialize($config);
        $this->setTable('courses_students');

        $this->belongsTo('Courses')
            ->setClassName('Fr3nch13/Utilities.Courses');
        $this->belongsTo('Students')
            ->setClassName('Fr3nch13/Utilities.Students');
    }
}
