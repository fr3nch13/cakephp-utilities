<?php
declare(strict_types=1);

/**
 * Book Entity
 *
 * These are here so I can test the Traits.
 */

namespace Fr3nch13\Utilities\Model\Entity;

/**
 * Book Entity
 *
 * @property int $id
 * @property string $name
 * @property int $student_id
 * @property \Fr3nch13\Utilities\Model\Entity\Student $student
 */
final class Book extends \Cake\ORM\Entity
{
}
