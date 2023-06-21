<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

final class InitialMigration extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        if ($this->getAdapter()->getAdapterType() == 'mysql') {
            $this->execute('SET UNIQUE_CHECKS = 0;');
            $this->execute('SET FOREIGN_KEY_CHECKS = 0;');
        }

        $table = $this->table('books');
        $table->addColumn('name', 'string', ['length' => '255'])
            ->addColumn('slug', 'string', ['length' => '255', 'null' => true])
            ->addColumn('student_id', 'integer', ['default' => null, 'null' => true])
            ->create();

        $table = $this->table('courses');
        $table->addColumn('name', 'string', ['length' => '255'])
            ->addColumn('name_other', 'string', ['length' => '255', 'null' => true])
            ->addColumn('slug', 'string', ['length' => '255', 'null' => true])
            ->addColumn('slug_other', 'string', ['length' => '255', 'null' => true])
            ->addColumn('updateme', 'string', ['length' => '255', 'null' => true])
            ->addColumn('available', 'boolean', ['default' => 1])
            ->addColumn('teachers_pet_id', 'integer', ['default' => null, 'null' => true])
            ->create();

        $table = $this->table('students');
        $table->addColumn('name', 'string', ['length' => '255'])
            ->addColumn('slug', 'string', ['length' => '255', 'null' => true])
            ->addColumn('updateme', 'string', ['length' => '255', 'null' => true])
            ->addColumn('alive', 'boolean', ['default' => 1])
            ->create();

        $table = $this->table('courses_students');
        $table->addColumn('course_id', 'integer')
            ->addColumn('student_id', 'integer')
            ->addColumn('grade', 'integer')
            ->create();

        if ($this->getAdapter()->getAdapterType() == 'mysql') {
            $this->execute('SET FOREIGN_KEY_CHECKS = 1;');
            $this->execute('SET UNIQUE_CHECKS = 1;');
        }
    }
}
