<?php
declare(strict_types=1);

namespace Fr3nch13\Utilities\Test\Fixture;

final class StudentsFixture extends \Cake\TestSuite\Fixture\TestFixture
{
    /**
     * Init method
     */
    public function init(): void
    {
        $io = new \Cake\Console\ConsoleIo();
        $io->out(__('--- Running Fixture: {0} ---', [self::class]));

        $this->table = 'students';

        $this->records = [
            ['id' => 1, 'name' => 'Harry', 'slug' => 'slug-1'],
            ['id' => 2, 'name' => 'Ron', 'slug' => 'slug-2'],
            ['id' => 3, 'name' => 'Hermione', 'slug' => 'slug-3'],
        ];

        parent::init();
    }
}
