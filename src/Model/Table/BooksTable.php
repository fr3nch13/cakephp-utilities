<?php
declare(strict_types=1);

/**
 * Books Model
 *
 * Used to test traits within a context for Student
 */

namespace Fr3nch13\Utilities\Model\Table;

/**
 * Books Model
 *
 * Used for testing the traits and behaviors.
 *
 * @property \Fr3nch13\Utilities\Model\Table\StudentsTable $Students
 * @method \Fr3nch13\Utilities\Model\Entity\Book get(mixed $primaryKey, array $options = [])
 * @method \Fr3nch13\Utilities\Model\Entity\Book newEntity($data = null, array $options = [])
 * @method \Fr3nch13\Utilities\Model\Entity\Book saveOrFail(\Fr3nch13\Utilities\Model\Entity\Book $entity, array $options = [])
 */
final class BooksTable extends \Cake\ORM\Table
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
        $this->setTable('books');

        $this->belongsTo('Students')
            ->setClassName('Fr3nch13/Utilities.Students');
    }
}
