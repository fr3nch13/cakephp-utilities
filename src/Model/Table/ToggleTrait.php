<?php
declare(strict_types=1);

/**
 * ToggleTrait
 */

namespace Fr3nch13\Utilities\Model\Table;

/**
 * Toggle Trait
 *
 * Common methods for toggling boolean fields in the database.
 */
trait ToggleTrait
{
    /**
     * Allows toggling of boolean fields.
     *
     * @param int $id The record id to update.
     * @param string $field The field to toggle.
     * @return bool The set toggled value
     * @throws \Cake\ORM\Exception\PersistenceFailedException
     * @TODO Use a more specific Exception when the save fails
     */
    public function toggle(int $id, string $field): bool
    {
        $result = $this->get($id);
        if ($result->get($field)) {
            $result->set($field, false);
        } else {
            $result->set($field, true);
        }

        $this->saveOrFail($result);

        return $result->get($field);
    }
}
