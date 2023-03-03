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
            ['id' => 1, 'name' => 'Potions', 'name_other' => 'PTNS', 'available' => 1],
            ['id' => 2, 'name' => 'Defence Against the Dark Arts', 'name_other' => 'DADA', 'available' => 1],
            ['id' => 3, 'name' => 'Charms', 'name_other' => 'CHMS', 'available' => 1],
        ];

        parent::init();
    }
}
