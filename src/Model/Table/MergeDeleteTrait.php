<?php
declare(strict_types=1);

/**
 * MergeDeleteTrait
 */

namespace Fr3nch13\Utilities\Model\Table;

use Cake\Utility\Inflector;

/**
 * Merge/Delete Trait
 *
 * Common methods merging one record into another, then deleting the first record.
 */
trait MergeDeleteTrait
{
    /**
     * Allows to merge associated records from one attribute to another, then delete the old record.
     *
     * @param int $sourceId The record id to delete.
     * @param int $targetId The record id to move the associated records to.
     * @return bool If the save was successfull.
     * @throws \Cake\ORM\Exception\PersistenceFailedException
     * @TODO Use a more specific Exception when the save fails
     */
    public function mergeDelete(int $sourceId, int $targetId): bool
    {
        $contain = [];
        $BelongsToMany = $this->associations()->getByType('belongsToMany');
        foreach ($BelongsToMany as $belongsToManyObj) {
            $underscore = Inflector::underscore($belongsToManyObj->getAlias());
            $contain[] = $belongsToManyObj->getAlias();
        }
        /** @property \Cake\Datasource\EntityInterface $sourceRecord */
        $sourceRecord = $this->get($sourceId, ['contain' => $contain]);

        // ensure the new record actually exists.
        /** @property \Cake\Datasource\EntityInterface $targetRecord */
        $targetRecord = $this->get($targetId, ['contain' => $contain]);

        /** @var \Cake\ORM\Association\BelongsToMany $belongsToManyObj */
        foreach ($BelongsToMany as $belongsToManyObj) {
            $underscore = Inflector::underscore($belongsToManyObj->getAlias());
            if ($sourceRecord->has($underscore)) {
                $table = $belongsToManyObj->junction();
                foreach ($sourceRecord->get($underscore) as $sourceEntity) {
                    $joinEntity = $sourceEntity->_joinData;
                    $joinEntity->set($belongsToManyObj->getForeignKey(), $targetId);
                    $table->saveOrFail($joinEntity);
                }
            }
        }

        $HasMany = $this->associations()->getByType('hasMany');
        foreach ($HasMany as $hasManyObj) {
            $foreignKey = $hasManyObj->getForeignKey();
            if (is_array($foreignKey)) {
                $foreignKey = array_shift($foreignKey);
            }
            $conditions = [$foreignKey => $sourceId];
            $count = $hasManyObj->find('all')
                ->where($conditions)
                ->count();
            if (!$count) {
                continue;
            }

            $fields = [$foreignKey => $targetId];
            $hasManyObj->updateAll($fields, $conditions);
        }

        return $this->delete($sourceRecord);
    }

    /**
     * Gets the list of available records for determining which record to merge to.
     * This is a custom finder.
     *
     * @param \Cake\ORM\Query<mixed> $query The query object to modify.
     * @param array<mixed> $options The options either specific to this finder, or to pass through. There should be an 'sourceId' option to exclude.
     * @return \Cake\ORM\Query<mixed> Return the modified query object.
     */
    public function findMergeRecords(\Cake\ORM\Query $query, array $options = []): \Cake\ORM\Query
    {
        $sourceId = null;
        if (isset($options['sourceId'])) {
            $sourceId = $options['sourceId'];
        }

        $primaryKey = $this->getPrimaryKey();
        if (isset($options['primaryKey'])) {
            $primaryKey = $options['primaryKey'];
        }
        $displayField = $this->getDisplayField();
        if (isset($options['displayField'])) {
            $displayField = $options['displayField'];
        }
        $query->find('list', [
            'keyField' => $options['keyField'] ?? $primaryKey,
            'valueField' => $options['valueField'] ?? $displayField,
        ]);
        if ($sourceId) {
            $query->where(function (\Cake\Database\Expression\QueryExpression $exp) use ($sourceId, $primaryKey) {
                return $exp->notEq($primaryKey, $sourceId);
            });
        }

        return $query;
    }

    /**
     * Get merge stats for a record to be deleted.
     *
     * @param int $id The id of the record.
     * @return array<string, array<string, string|int>> The list of stats.
     */
    public function getMergeStats(int $id): array
    {
        $stats = [];

        $HasMany = $this->associations()->getByType('hasMany');
        foreach ($HasMany as $hasManyObj) {
            $foreignKey = $hasManyObj->getForeignKey();
            if (is_array($foreignKey)) {
                $foreignKey = array_shift($foreignKey);
            }
            $stats[$hasManyObj->getAlias()] = [
                'name' => $hasManyObj->getAlias(),
                'count' => $hasManyObj->find('all')
                    ->where([$hasManyObj->getAlias() . '.' . $foreignKey => $id])
                    ->count(),
            ];
        }

        $BelongsToMany = $this->associations()->getByType('belongsToMany');
        foreach ($BelongsToMany as $belongsToManyObj) {
            /** @var string $primaryKey */
            $primaryKey = $this->getPrimaryKey();
            $stats[$belongsToManyObj->getAlias()] = [
                'name' => $belongsToManyObj->getAlias(),
                'count' => $belongsToManyObj->find('all')
                    ->matching($this->getAlias(), function ($q) use ($id, $primaryKey) {
                        return $q->where([$this->getAlias() . '.' . $primaryKey => $id]);
                    })
                    ->count(),
            ];
        }

        return $stats;
    }
}
