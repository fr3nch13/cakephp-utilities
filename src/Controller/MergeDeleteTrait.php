<?php
declare(strict_types=1);

/**
 * MergeDeleteTrait
 */

namespace Fr3nch13\Utilities\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

/**
 * Merge/Delete Trait
 *
 * Common methods merging one record into another, then deleting the first record.
 */
trait MergeDeleteTrait
{
    use FuModelFindTrait;

    /**
     * The merge-delete display field of the model.
     * Can be either real, or virtual.
     *
     * @var null|string
     */
    public $mdDisplayField = null;

    /**
     * Allows to merge associated records from one attribute to another, then delete the old record.
     *
     * @param int $sourceId The record id to delete.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function mergeDelete($sourceId): ?\Cake\Http\Response
    {
        $this->traitModelFind();

        try {
            $sourceRecord = $this->{$this->fuModelAlias}->get($sourceId);
        } catch (\Exception $e) {
            throw new NotFoundException(__('Unable to find the old record to merge/delete.'));
        }

        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $data = $this->getRequest()->getData();

            if (!isset($data['id']) || !$data['id']) {
                throw new NotFoundException(__('No new record was selected.'));
            }
            if (is_string($data['id'])) {
                $data['id'] = intval($data['id']);
            }

            try {
                $result = $this->{$this->fuModelAlias}->mergeDelete($sourceRecord->get('id'), $data['id']);
                if ($result) {
                    $this->Flash->success(__('The Old record has been merged into the new record, and deleted.'));
                    $redirect = ['action' => 'index'];
                    if (isset($data['referer'])) {
                        $redirect = $data['referer'];
                    }

                    return $this->redirect($redirect);
                }
            } catch (\Exception $e) {
                throw new NotFoundException(__('Unable to merge the old record into ' .
                    'the new record. Ensure both records exist.'));
            }
        }

        $displayField = $this->{$this->fuModelAlias}->getDisplayField();
        // used for testing but should be set in the controller, not through Configure.
        if (Configure::check('mdDisplayField')) {
            $this->mdDisplayField = Configure::read('mdDisplayField');
        }

        if ($this->mdDisplayField) {
            $displayField = $this->mdDisplayField;
        }

        $records = $this->{$this->fuModelAlias}->find('mergeRecords', [
            'sourceId' => $sourceRecord->get('id'),
            'displayField' => $displayField,
        ]);
        $stats = $this->{$this->fuModelAlias}->getMergeStats($sourceRecord->get('id'));

        $this->set([
            'sourceRecord' => $sourceRecord,
            'displayField' => $displayField,
            'records' => $records,
            'stats' => $stats,
        ]);

        return null;
    }
}
