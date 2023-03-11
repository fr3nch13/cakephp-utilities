<?php
declare(strict_types=1);

/**
 * Apply Settings Trait
 *
 * Adds the ability to batch change multiple fields.
 */

namespace Fr3nch13\Utilities\Model\Table;

use Cake\Database\Expression\QueryExpression;

/**
 * Be careful using this as it can easily abused since it doesn't do any complex data validation.
 */
trait ApplySettingsTrait
{
    /**
     * @var string The error if one happens.
     */
    public $applyError = '';

    /**
     * Allows changing of multiple fields in batch by their unique ids.
     *
     * @param array<string, mixed> $data The data from the form with the apply key set in it.
     * @param array<int, int> $ids The unique ids that will have these changes applied.
     * @param null|\Cake\ORM\Entity $entity The entity to attach to records
     * @param null|string $record_field The field on the records that the entity should be attached to.
     * @return null|int If the save was successfull, return the record count.
     * @throws \Cake\ORM\Exception\PersistenceFailedException
     * @TODO Use a more specific Exception when the save fails
     */
    public function applySettings(
        array $data = [],
        array $ids = [],
        ?\Cake\ORM\Entity $entity = null,
        ?string $record_field = null
    ): ?int {
        $fields = [];
        $this->setApplyError('');

        if (!isset($data['apply']) || !is_array($data['apply'])) {
            $this->setApplyError(__('The data is malformed.'));

            return null;
        }

        $columns = $this->getSchema()->columns();

        foreach ($data['apply'] as $field => $applyThis) {
            if ($applyThis && isset($data[$field]) && in_array($field, $columns)) {
                $fields[$field] = $data[$field];
            }
        }

        // get all of the records by ids
        /** @var string $primaryKey */
        $primaryKey = $this->getPrimaryKey();
        $records = $this->find('all')
            ->where(function (QueryExpression $exp) use ($ids, $primaryKey) {
                return $exp->in($primaryKey, $ids);
            });

        $i = 0;
        foreach ($records as $record) {
            foreach ($fields as $field => $value) {
                if ($record->get($field) !== $value) {
                    $record->set($field, $value);
                }
            }
            if ($record->isDirty()) {
                if ($entity && $record_field) {
                    $record->set($record_field, $entity);
                }
                $this->saveOrFail($record);
                $i++;
            }
        }

        return $i;
    }

    /**
     * Sets an error if one happens.
     *
     * @param string $msg The error message.
     * @return void
     */
    public function setApplyError(string $msg): void
    {
        $this->applyError = $msg;
    }

    /**
     * Gets an error is one happened.
     *
     * @return string The error message.
     */
    public function getApplyError(): string
    {
        return $this->applyError;
    }
}
