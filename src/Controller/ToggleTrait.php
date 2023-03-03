<?php
declare(strict_types=1);

/**
 * MergeDeleteTrait
 */

namespace Fr3nch13\Utilities\Controller;

use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Inflector;

/**
 * Merge/Delete Trait
 *
 * Common methods for toggling boolean fields in the database.
 */
trait ToggleTrait
{
    use FuModelFindTrait;

    /**
     * Allows toggling of boolean fields
     *
     * @param mixed $id The record id to update
     * @param string $field The field to toggle
     * @param null|string $action The action to send the user back to
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function toggle($id, string $field, ?string $action = 'index'): ?\Cake\Http\Response
    {
        $this->traitModelFind();

        try {
            $result = $this->{$this->fuModelAlias}->toggle((int)$id, $field);
        } catch (\Exception $e) {
            throw new NotFoundException(__('Unable to find the record to toggle.'));
        }

        if ($this->getRequest()->is('json')) {
            $this->set('result', $result);
            $this->viewBuilder()->setOption('serialize', ['result']);
        } else {
            $this->Flash->success(__('The {0} has been updated.', [
                $this->fuModelAlias,
            ]));

            return $this->redirect($this->getReferer());
        }

        return null;
    }

    /**
     * Allows toggling of boolean fields
     *
     * @param int $id The record id to update
     * @param string $field The field to toggle
     * @param bool|null $direct Check if directly set if true, if group if false, considered if null
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function boolCheck($id, string $field, ?bool $direct = null): ?\Cake\Http\Response
    {
        $this->traitModelFind();

        try {
            /** @var \Cake\ORM\Entity $object */
            $object = $this->{$this->fuModelAlias}->get($id);
        } catch (\Exception $e) {
            throw new NotFoundException(__('Unable to find the record to check.'));
        }

        $result = null;
        $typeCheck = null;
        $fieldOther = Inflector::variable($field);

        if ($direct !== null) {
            $direct = ($direct ? true : false);
        }

        if (method_exists($object, 'boolCheck')) {
            $typeCheck = 'boolCheck';
            $result = $object->boolCheck($field, $direct);
        } elseif (method_exists($object, $field)) {
            $typeCheck = 'method';
            $result = ($object->{$field}($direct) ? true : false);
        } elseif (method_exists($object, $fieldOther)) {
            $typeCheck = 'methodOther';
            $result = ($object->{$fieldOther}($direct) ? true : false);
        } elseif ($object->has($field)) {
            $typeCheck = 'field';
            $result = ($object->{$field} ? true : false);
        }

        if ($this->getRequest()->is('json')) {
            $this->set([
                'result' => $result,
                'direct' => $direct,
                'typeCheck' => $typeCheck,
                'field' => $field,
                'model' => $this->fuModel,
            ]);
            $this->viewBuilder()->setOption('serialize', ['result', 'direct', 'typeCheck', 'field', 'model']);
        } else {
            $this->Flash->success(__('The {0}\'s field `{1}` is {2}.', [
                $this->fuModel, $field, ($result ? __('True') : __('False')),
            ]));

            return $this->redirect($this->getReferer());
        }

        return null;
    }
}
