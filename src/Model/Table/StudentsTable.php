<?php
declare(strict_types=1);

/**
 * Students Model
 *
 * Used to test traits within a context for Student
 */

namespace Fr3nch13\Utilities\Model\Table;

/**
 * Students Model
 *
 * Used for testing the traits and behaviors.
 *
 * @mixin \Fr3nch13\Utilities\Model\Behavior\MemoryBehavior
 * @mixin \Fr3nch13\Utilities\Model\Behavior\SluggableBehavior
 * @property \Fr3nch13\Utilities\Model\Table\BooksTable $Books
 * @property \Fr3nch13\Utilities\Model\Table\CoursesTable $Courses
 * @property \Fr3nch13\Utilities\Model\Table\CoursesStudentsTable $CoursesStudents
 * @method \Fr3nch13\Utilities\Model\Entity\Student get(mixed $primaryKey, array $options = [])
 * @method \Fr3nch13\Utilities\Model\Entity\Student newEntity($data = null, array $options = [])
 * @method \Fr3nch13\Utilities\Model\Entity\Student saveOrFail(\Fr3nch13\Utilities\Model\Entity\Student|\Cake\ORM\Entity $entity, array $options = [])
 * @method \Fr3nch13\Utilities\Model\Entity\Student checkAdd(mixed $name, string $slug = null, array $fields = [], bool|array $returnEntity = false)
 */
final class StudentsTable extends \Cake\ORM\Table
{
    use ApplySettingsTrait;
    use CheckAddTrait;
    use MergeDeleteTrait;
    use ToggleTrait;

    /**
     * Initialize method
     *
     * @param array<mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config = []): void
    {
        parent::initialize($config);
        $this->setTable('students');

        $this->hasMany('Books')
            ->setClassName('Fr3nch13/Utilities.Books');

        $this->belongsToMany('Courses')
            ->setClassName('Fr3nch13/Utilities.Courses')
            ->setThrough('Fr3nch13/Utilities.CoursesStudents');

        $this->addBehavior('Fr3nch13/Utilities.Memory');
    }
}
