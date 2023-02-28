<?php
declare(strict_types=1);

namespace Fr3nch13\Utilities\Test\Fixture;

final class CoursesFixture extends \Cake\TestSuite\Fixture\TestFixture
{
    /**
     * Init method
     */
    public function init(): void
    {
        $io = new \Cake\Console\ConsoleIo();
        $io->out(__('--- Running Fixture: {0} ---', [self::class]));

        $this->table = 'courses';

        $this->records = [
            ['id' => 1, 'name' => 'Potions', 'available' => 1],
            ['id' => 2, 'name' => 'Defence Against the Dark Arts', 'available' => 1],
            ['id' => 3, 'name' => 'Charms', 'available' => 1],
        ];

        parent::init();
    }
}
