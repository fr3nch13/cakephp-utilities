<?php
declare(strict_types=1);

/**
 * Course Entity
 *
 * These are here so I can test the Traits.
 */

namespace Fr3nch13\Utilities\Model\Entity;

/**
 * Course Entity
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $updateme
 * @property int $available
 * @property int $teachers_pet_id
 * @property \Fr3nch13\Utilities\Model\Entity\Student[] $students
 * @property \Fr3nch13\Utilities\Model\Entity\Student $teachers_pet
 */
final class Course extends \Cake\ORM\Entity
{
}
