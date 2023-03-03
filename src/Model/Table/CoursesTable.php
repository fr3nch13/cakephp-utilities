<?php
declare(strict_types=1);

/**
 * Courses Model
 *
 * Used to test traits within a context for Courses
 */

namespace Fr3nch13\Utilities\Model\Table;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;

/**
 * Courses Model
 *
 * Used for testing the traits and behaviors.
 *
 * @mixin \Fr3nch13\Utilities\Model\Behavior\MemoryBehavior
 * @mixin \Fr3nch13\Utilities\Model\Behavior\SluggableBehavior
 * @property \Fr3nch13\Utilities\Model\Table\StudentsTable $Students
 * @property \Fr3nch13\Utilities\Model\Table\CoursesStudentsTable $CoursesStudents
 * @property \Fr3nch13\Utilities\Model\Table\StudentsTable $TeachersPet
 * @method \Fr3nch13\Utilities\Model\Entity\Course get(mixed $primaryKey, array $options = [])
 * @method \Fr3nch13\Utilities\Model\Entity\Course newEntity($data = null, array $options = [])
 * @method \Fr3nch13\Utilities\Model\Entity\Course saveOrFail(\Fr3nch13\Utilities\Model\Entity\Course|\Cake\ORM\Entity $entity, array $options = [])
 * @method \Fr3nch13\Utilities\Model\Entity\Course checkAdd(mixed $name, string $slug = null, array $fields = [], bool|array $returnEntity = false)
 */
final class CoursesTable extends \Cake\ORM\Table
{
    /**
     * Not used here or in unit testing,
     * but here for phpstan to have multiple viewpoints
     * for analyzing the traits.
     */
    use MergeDeleteTrait;

    /**
     * Actually tested in the unit tests.
     */
    use ApplySettingsTrait;
    use CheckAddTrait;
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
        $this->setTable('courses');

        $this->belongsToMany('Students')
            ->setClassName('Fr3nch13/Utilities.Students')
            ->setThrough('Fr3nch13/Utilities.CoursesStudents');

        $this->hasOne('TeachersPet')
            ->setProperty('teachers_pet')
            ->setForeignKey('teachers_pet_id')
            ->setClassName('Fr3nch13/Utilities.Students')
            ->setDependent(false);

        $this->addBehavior('Fr3nch13/Utilities.Memory');
        $this->addBehavior('Fr3nch13/Utilities.Sluggable');

        $this->initCheckAdd();
    }

    /**
     * Finder methond to get all available Courses.
     *
     * @param \Cake\ORM\Query<mixed> $query The query object to modify.
     * @param array<mixed> $options The options either specific to this finder, or to pass through.
     * @return \Cake\ORM\Query<mixed> Return the modified query object.
     */
    public function findAvailable(Query $query, array $options = []): Query
    {
        return $query->where(function (QueryExpression $exp) {
            return $exp->eq('Courses.available', 1);
        });
    }

    /**
     * Fixes the name if we need to.
     *
     * @param string $name The string to fix.
     * @return string The fixed name
     */
    public function fixName(string $name): string
    {
        return trim($name);
    }
}
