<?php
declare(strict_types=1);

/**
 * Student Entity
 *
 * These are here so I can test the Traits.
 */

namespace Fr3nch13\Utilities\Model\Entity;

/**
 * Student Entity
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Fr3nch13\Utilities\Model\Entity\Book[] $books
 */
final class Student extends \Cake\ORM\Entity
{
}
