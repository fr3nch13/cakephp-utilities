<?php
declare(strict_types=1);

namespace Fr3nch13\Utilities\Test\Fixture;

/**
 * Yes, ok before this goes out into the open world:
 * I didn't look up if they were in these courses, or what their grades were.
 * I just needed to put some sample data in the database for testing.
 * If you really feel the need to make this correct with the books, submit a pull request.
 */
final class CoursesStudentsFixture extends \Cake\TestSuite\Fixture\TestFixture
{
    /**
     * Init method
     */
    public function init(): void
    {
        $io = new \Cake\Console\ConsoleIo();
        $io->out(__('--- Running Fixture: {0} ---', [self::class]));

        $this->table = 'courses_students';

        $this->records = [
            ['id' => 1, 'course_id' => 1, 'student_id' => 1, 'grade' => 100],
            ['id' => 4, 'course_id' => 2, 'student_id' => 1, 'grade' => 100],
            ['id' => 7, 'course_id' => 3, 'student_id' => 1, 'grade' => 100],
            ['id' => 2, 'course_id' => 1, 'student_id' => 2, 'grade' => 80],
            ['id' => 5, 'course_id' => 2, 'student_id' => 2, 'grade' => 80],
            ['id' => 3, 'course_id' => 1, 'student_id' => 3, 'grade' => 100],
            ['id' => 6, 'course_id' => 2, 'student_id' => 3, 'grade' => 100],
            ['id' => 9, 'course_id' => 3, 'student_id' => 3, 'grade' => 100],
        ];

        parent::init();
    }
}
