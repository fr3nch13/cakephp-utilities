<?php
declare(strict_types=1);

/**
 * CoursesController
 */

namespace Fr3nch13\Utilities\Controller;

use Cake\Core\Configure;

/**
 * Courses Controller
 *
 *  Used to test the Controller Toggle Trait.
 *
 * @property \Cake\Controller\Component\RequestHandlerComponent $RequestHandler
 * @property \Fr3nch13\Utilities\Model\Table\CoursesTable $Courses
 */
class CoursesController extends AppController
{
    use MergeDeleteTrait;
    use ToggleTrait;

    /**
     * Setup stuff for the actions to use
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        if (Configure::check('LoadFlash') && Configure::read('LoadFlash')) {
            $this->loadComponent('Flash');
        }
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index(): void
    {
        $courses = $this->Courses->find('all')->all();
        $this->set(compact('courses'));
    }
}
