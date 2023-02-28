<?php
declare(strict_types=1);

namespace Fr3nch13\Utilities\Test\Fixture;

final class BooksFixture extends \Cake\TestSuite\Fixture\TestFixture
{
    /**
     * Init method
     */
    public function init(): void
    {
        $io = new \Cake\Console\ConsoleIo();
        $io->out(__('--- Running Fixture: {0} ---', [self::class]));

        $this->table = 'books';

        $this->records = [
            ['id' => 1, 'name' => "Sorcerer's Stone", 'student_id' => 1],
            ['id' => 2, 'name' => 'Chamber of Secrets', 'student_id' => 1],
            ['id' => 3, 'name' => 'Prisoner of Azkaban', 'student_id' => 1],
            ['id' => 4, 'name' => 'Goblet Of Fire', 'student_id' => 1],
            ['id' => 5, 'name' => 'Half-Blood Prince', 'student_id' => 1],
            ['id' => 6, 'name' => 'Order of the Phoenix', 'student_id' => 1],
            ['id' => 7, 'name' => 'Deathly Hallows', 'student_id' => 1],
        ];

        parent::init();
    }
}
