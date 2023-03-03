<?php
declare(strict_types=1);

/**
 * FuModelFindTrait
 */

namespace Fr3nch13\Utilities\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

trait FuModelFindTrait
{
    /**
     * The name of the model we're using, if it's not set,
     * then it'll use the name of the controller, to try to figure it out.
     *
     * @var null|string The name of the model.
     */
    public $fuModel = null;

    /**
     * @var null|string The model alias to use.
     */
    public $fuModelAlias = null;

    /**
     * Used to find the model for the other taits here.

     * @return void
     */
    public function traitModelFind(): void
    {
        // try to figure out what model to use from the controller name.
        if (!$this->fuModel) {
            $this->fuModel = $this->defaultTable;
            // used for testing but should be set in the controller, not through Configure.
            if (Configure::check('fuModel')) {
                $this->fuModel = Configure::read('fuModel');
            }
        }

        // If we're in a plugin, see if we can load the overloaded model from the app, if it exists.
        // This is incase there are other relationships we need to account for.
        $this->fuModelAlias = $this->fuModel;
        if (strpos($this->fuModel, '.') !== false) {
            $parts = explode('.', $this->fuModel);
            $this->fuModelAlias = array_pop($parts);
            // thows an exception if TableLocator::allowFallbackClass(false)
            try {
                $table = $this->fetchTable($this->fuModelAlias);
                if (get_class($table) != 'Cake\ORM\Table') {
                    $this->{$this->fuModelAlias} = $table;
                }
            } catch (\Exception $e) {
                // pass
            }
        }

        // if it wasn't found as a model in app (aka, not overloaded), then use the plugin model.
        if (!isset($this->{$this->fuModelAlias})) {
            try {
                $this->{$this->fuModelAlias} = $this->fetchTable($this->fuModel);
            } catch (\Exception $e) {
                // pass
            }
        }

        // if we can't figure it out, throw an error to the user.
        // This looks for CahePHP's autoloading of tables.
        if (!isset($this->{$this->fuModelAlias}) || get_class($this->{$this->fuModelAlias}) == 'Cake\ORM\Table') {
            throw new NotFoundException(__('Unable to find the model `{0}` (alias:{1}) to use to toggle.', [
                $this->fuModel,
                $this->fuModelAlias,
            ]));
        }
    }
}
