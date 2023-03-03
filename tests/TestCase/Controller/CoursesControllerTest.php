<?php
declare(strict_types=1);

/**
 * CoursesControllerTest
 */
namespace Fr3nch13\Utilities\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * CoursesControllerTest class
 *
 * Used for testing the Controller Traits within an actual controller.
 */
class CoursesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Defines which fixtures we'll be using.
     *
     * @return array<string>
     */
    public function getFixtures(): array
    {
        $fixtures = [
            'plugin.Fr3nch13/Utilities.Books',
            'plugin.Fr3nch13/Utilities.Courses',
            'plugin.Fr3nch13/Utilities.Students',
            'plugin.Fr3nch13/Utilities.CoursesStudents',
        ];

        return $fixtures;
    }

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Configure::write('LoadFlash', true);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * test index method
     *
     * @return void
     */
    public function testCoursesIndexGet(): void
    {
        Configure::write('debug', true);
        $this->get('/fr3nch13u/courses');

        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('<span class="name">Potions</span>');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesMergeDeleteGet(): void
    {
        Configure::write('debug', true);
        $this->get('/fr3nch13u/courses/merge-delete/1');

        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('<h1><a href="">Merge/Delete a Record</a></h1>');
        $this->assertResponseContains('<h3>Old Record: Potions</h3>');
        $this->assertResponseContains('<span class="name">Students</span>');
        $this->assertResponseContains('<span class="count">3</span>');
        $this->assertResponseNotContains('<option value="1" selected="selected">Potions</option>');
        $this->assertResponseContains('<option value="2">Defence Against the Dark Arts</option>');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesMergeDeleteGetNoFallback(): void
    {
        Configure::write('debug', true);
        /** @var \Cake\ORM\Locator\TableLocator $Locator */
        $Locator = $this->getTableLocator();
        $Locator->allowFallbackClass(false);

        $this->get('/fr3nch13u/courses/merge-delete/1');

        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('<h1><a href="">Merge/Delete a Record</a></h1>');
        $this->assertResponseContains('<h3>Old Record: Potions</h3>');
        $this->assertResponseContains('<span class="name">Students</span>');
        $this->assertResponseContains('<span class="count">3</span>');
        $this->assertResponseNotContains('<option value="1" selected="selected">Potions</option>');
        $this->assertResponseContains('<option value="2">Defence Against the Dark Arts</option>');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesMergeDeleteGetDisplayField(): void
    {
        Configure::write('debug', true);
        Configure::write('mdDisplayField', 'name_other');
        $this->get('/fr3nch13u/courses/merge-delete/1');

        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertResponseContains('<h1><a href="">Merge/Delete a Record</a></h1>');
        $this->assertResponseContains('<h3>Old Record: PTNS</h3>');
        $this->assertResponseContains('<span class="name">Students</span>');
        $this->assertResponseContains('<span class="count">3</span>');
        $this->assertResponseNotContains('<option value="1" selected="selected">PTNS</option>');
        $this->assertResponseContains('<option value="2">DADA</option>');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesMergeDeleteUnknownID(): void
    {
        Configure::write('debug', true);
        $this->get('/fr3nch13u/courses/merge-delete/10');

        $this->assertResponseNotEmpty();
        $this->assertResponseCode(404);
        $this->assertResponseError('Unable to find the old record to merge/delete.');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesMergeDeleteGetMissingId(): void
    {
        Configure::write('debug', true);
        $this->get('/fr3nch13u/courses/merge-delete');

        $this->assertResponseNotEmpty();
        $this->assertResponseCode(404);
        $this->assertResponseError('Missing passed parameter for `sourceId` in action Courses::mergeDelete().');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesMergeDeleteGetBadModel(): void
    {
        Configure::write('debug', true);
        Configure::write('fuModel', 'Dontexists');
        $this->get('/fr3nch13u/courses/merge-delete/1');

        //$content = (string)$this->_response->getBody();
        //debug($content);

        $this->assertResponseNotEmpty();
        $this->assertResponseCode(404);
        $this->assertResponseError('Table class for alias `Dontexists` could not be found.');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesMergeDeletePost(): void
    {
        Configure::write('debug', true);
        $this->enableRetainFlashMessages();

        $this->post('/fr3nch13u/courses/merge-delete/1', ['id' => 2]);

        $this->assertRedirect(['controller' => 'Courses', 'action' => 'index']);
        $this->assertSession('The Old record has been merged into the new record, and deleted.', 'Flash.flash.0.message');

        $this->get('/fr3nch13u/courses/merge-delete/1');

        $this->assertResponseNotEmpty();
        $this->assertResponseCode(404);
        $this->assertResponseError('Unable to find the old record to merge/delete.');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesMergeDeletePostReferer(): void
    {
        Configure::write('debug', true);
        $this->enableRetainFlashMessages();

        $this->post('/fr3nch13u/courses/merge-delete/1', ['id' => 2, 'referer' => ['action' => 'view', 2]]);

        $this->assertRedirect(['controller' => 'Courses', 'action' => 'view', 2]);
        $this->assertSession('The Old record has been merged into the new record, and deleted.', 'Flash.flash.0.message');

        $this->get('/fr3nch13u/courses/merge-delete/1');

        $this->assertResponseNotEmpty();
        $this->assertResponseCode(404);
        $this->assertResponseError('Unable to find the old record to merge/delete.');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesMergeDeletePostNoTarget(): void
    {
        Configure::write('debug', true);
        $this->enableRetainFlashMessages();

        $this->post('/fr3nch13u/courses/merge-delete/1', ['id' => 2]);

        $this->assertRedirect(['controller' => 'Courses', 'action' => 'index']);
        $this->assertSession('The Old record has been merged into the new record, and deleted.', 'Flash.flash.0.message');

        $this->get('/fr3nch13u/courses/merge-delete/1');

        $this->assertResponseNotEmpty();
        $this->assertResponseCode(404);
        $this->assertResponseError('Unable to find the old record to merge/delete.');

        $this->post('/fr3nch13u/courses/merge-delete/2', ['id' => 1]); // 1 no longer exists.

        $this->assertResponseNotEmpty();
        $this->assertResponseCode(404);
        $this->assertResponseError('Record not found in table "courses"');
        $this->assertSession('The Old record has been merged into the new record, and deleted.', 'Flash.flash.0.message');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesMergeDeletePostNoId(): void
    {
        Configure::write('debug', true);
        $this->post('/fr3nch13u/courses/merge-delete/1', []);
        $this->assertResponseNotEmpty();
        $this->assertResponseCode(404);
        $this->assertResponseError('No new record was selected.');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesToggle(): void
    {
        Configure::write('debug', true);
        /** @var \Cake\ORM\Locator\TableLocator $Locator */
        $Locator = $this->getTableLocator();

        /** @var \Fr3nch13\Utilities\Model\Table\CoursesTable $Courses */
        $Courses = $Locator->get('Fr3nch13/Utilities.Courses');

        $course = $Courses->get(1);
        $this->assertSame(true, $course->get('available'));

        $this->configRequest([
            'environment' => ['HTTP_REFERER' => \Cake\Routing\Router::url('fr3nch13u/courses', true)],
        ]);

        $this->get('/fr3nch13u/courses/toggle/1/available');
        $this->assertRedirect(['controller' => 'Courses', 'action' => 'index']);

        $course = $Courses->get(1);
        $this->assertSame(false, $course->get('available'));
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesToggleJson(): void
    {
        Configure::write('debug', true);

        $this->get('/fr3nch13u/courses/toggle/1/available.json');

        $content = (string)$this->_response->getBody();
        $content = json_decode($content);

        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertFalse($content->result);
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesToggleBadId(): void
    {
        Configure::write('debug', true);

        $this->get('/fr3nch13u/courses/toggle/10/available');
        $this->assertResponseNotEmpty();
        $this->assertResponseCode(404);
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesToggleNoFallback(): void
    {
        Configure::write('debug', true);
        /** @var \Cake\ORM\Locator\TableLocator $Locator */
        $Locator = $this->getTableLocator();
        $Locator->allowFallbackClass(false);
        /** @var \Cake\ORM\Locator\TableLocator $Locator */
        $Locator = $this->getTableLocator();

        /** @var \Fr3nch13\Utilities\Model\Table\CoursesTable $Courses */
        $Courses = $Locator->get('Fr3nch13/Utilities.Courses');

        $course = $Courses->get(1);
        $this->assertSame(true, $course->get('available'));

        $this->configRequest([
            'environment' => ['HTTP_REFERER' => \Cake\Routing\Router::url('fr3nch13u/courses', true)],
        ]);

        $this->get('/fr3nch13u/courses/toggle/1/available');
        $this->assertRedirect(['controller' => 'Courses', 'action' => 'index']);

        $course = $Courses->get(1);
        $this->assertSame(false, $course->get('available'));
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesToggleGetBadModel(): void
    {
        Configure::write('debug', true);
        Configure::write('fuModel', 'Dontexists');
        $this->get('/fr3nch13u/courses/toggle/1/available');

        $content = (string)$this->_response->getBody();
        //debug($content);
        //debug($this->_response);

        $this->assertResponseNotEmpty();
        $this->assertResponseCode(404);
        $this->assertResponseError('Table class for alias `Dontexists` could not be found.');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesToggleGetBadModelNoFallback(): void
    {
        Configure::write('debug', true);
        /** @var \Cake\ORM\Locator\TableLocator $Locator */
        $Locator = $this->getTableLocator();
        $Locator->allowFallbackClass(false);
        Configure::write('fuModel', 'Dontexists');
        $this->get('/fr3nch13u/courses/toggle/1/available');

        $content = (string)$this->_response->getBody();
        //debug($content);
        //debug($this->_response);

        $this->assertResponseNotEmpty();
        $this->assertResponseCode(404);
        $this->assertResponseError('Table class for alias `Dontexists` could not be found.');
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesToggleBoolCheck(): void
    {
        Configure::write('debug', true);

        $this->configRequest([
            'environment' => ['HTTP_REFERER' => \Cake\Routing\Router::url('fr3nch13u/courses', true)],
        ]);

        $this->get('/fr3nch13u/courses/bool-check/1/available');
        $this->assertRedirect(['controller' => 'Courses', 'action' => 'index']);
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesToggleBoolCheckReferer(): void
    {
        Configure::write('debug', true);

        $this->get('/fr3nch13u/courses/bool-check/1/available?referer=' . urlencode('fr3nch13u/courses'));
        $this->assertRedirect(['controller' => 'Courses', 'action' => 'index']);

        $this->get('/fr3nch13u/courses/bool-check/1/available?referer=' . urlencode('fr3nch13u/courses/view/2?tab=new%20students'));
        $this->get('/fr3nch13u/courses/bool-check/1/available?referer=' . urlencode('fr3nch13u/courses/view/2?tab=new+students'));
        $this->assertRedirect(['controller' => 'Courses', 'action' => 'view', 2, '?' => ['tab' => 'new students']]);
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesToggleBoolCheckJson(): void
    {
        Configure::write('debug', true);

        $this->get('/fr3nch13u/courses/bool-check/1/available.json');

        $content = (string)$this->_response->getBody();
        $content = json_decode($content);

        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTrue($content->result);
        $this->assertNull($content->direct);
        $this->assertSame('available', $content->field);
        $this->assertSame('Fr3nch13/Utilities.Courses', $content->model);
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesToggleBoolCheckDirectJson(): void
    {
        Configure::write('debug', true);

        $this->get('/fr3nch13u/courses/bool-check/1/available/1.json');

        $content = (string)$this->_response->getBody();
        $content = json_decode($content);

        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTrue($content->result);
        $this->assertTrue($content->direct);
        $this->assertSame('available', $content->field);
        $this->assertSame('Fr3nch13/Utilities.Courses', $content->model);

        $this->get('/fr3nch13u/courses/bool-check/1/available/0.json');

        $content = (string)$this->_response->getBody();
        $content = json_decode($content);

        $this->assertResponseOk();
        $this->assertResponseNotEmpty();
        $this->assertTrue($content->result);
        $this->assertFalse($content->direct);
        $this->assertSame('available', $content->field);
        $this->assertSame('Fr3nch13/Utilities.Courses', $content->model);
    }

    /**
     * test merge delete method
     *
     * @return void
     */
    public function testCoursesToggleBoolCheckBadId(): void
    {
        Configure::write('debug', true);

        $this->get('/fr3nch13u/courses/bool-check/10/available');
        $this->assertResponseNotEmpty();
        $this->assertResponseCode(404);
    }
}
