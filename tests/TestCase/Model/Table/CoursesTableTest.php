<?php
declare(strict_types=1);

/**
 * CoursesTableTest
 */

namespace Fr3nch13\Utilities\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;

/**
 * CoursesTable Test
 */
class CoursesTableTest extends TestCase
{
    /**
     * @var \Fr3nch13\Utilities\Model\Table\CoursesTable The table object.
     */
    public $Courses;

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
     * Connect the model.
     */
    public function setUp(): void
    {
        parent::setUp();

        /** @var \Cake\ORM\Locator\TableLocator $Locator */
        $Locator = $this->getTableLocator();
        $Locator->allowFallbackClass(false);

        /** @var \Fr3nch13\Utilities\Model\Table\CoursesTable $Courses */
        $Courses = $Locator->get('Fr3nch13/Utilities.Courses');
        $this->Courses = $Courses;
    }

    /**
     * Tests the class name of the Table
     *
     * @return void
     */
    public function testClassInstance(): void
    {
        $this->assertInstanceOf(\Fr3nch13\Utilities\Model\Table\CoursesTable::class, $this->Courses);
    }

    /**
     * Testing a method.
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertEquals('courses', $this->Courses->getTable());
        $this->assertEquals('name', $this->Courses->getDisplayField());
        $this->assertEquals('id', $this->Courses->getPrimaryKey());
    }

    /**
     * Test the behaviors
     *
     * @return void
     */
    public function testBehaviors(): void
    {
        $behaviors = [
            'Memory' => \Fr3nch13\Utilities\Model\Behavior\MemoryBehavior::class,
            'Sluggable' => \Fr3nch13\Utilities\Model\Behavior\SluggableBehavior::class,
        ];
        foreach ($behaviors as $name => $class) {
            $behavior = $this->Courses->behaviors()->get($name);
            $this->assertNotNull($behavior, __('Behavior `{0}` is null.', [$name]));
            $this->assertInstanceOf($class, $behavior, __('Behavior `{0}` isn\'t an instance of {1}.', [
                $name,
                $class,
            ]));
        }
    }

    /**
     * Test Associations method
     *
     * @return void
     */
    public function testAssociations(): void
    {
        // get all of the associations
        $Associations = $this->Courses->associations();

        // make sure the association exists
        $this->assertNotNull($Associations->get('Students'));
        $this->assertInstanceOf(\Cake\ORM\Association\BelongsToMany::class, $Associations->get('Students'));
        $Association = $Associations->get('Students');
        $this->assertSame('Students', $Association->getName());
        $this->assertSame('course_id', $Association->getForeignKey());
        $this->assertSame('student_id', $Association->getTargetForeignKey());
        $this->assertSame('Fr3nch13/Utilities.Students', $Association->getClassName());
    }

    /**
     * Test the entity itself
     *
     * @return void
     */
    public function testEntity(): void
    {
        $entity = $this->Courses->get(3, [
            'contain' => [
                'Students',
            ],
        ]);

        $this->assertSame('Charms', $entity->get('name'));
    }

    /**
     * Test the apply settting trait itself
     *
     * @return void
     */
    public function testApplySettingsTrait(): void
    {
        $entities = $this->Courses->find('available');
        $this->assertSame(3, $entities->count());

        $this->Courses->applySettings(
            ['available' => 0],
            [2, 3]
        );

        $entities = $this->Courses->find('available');
        $this->assertSame(3, $entities->count());
        $this->assertSame('The data is malformed.', $this->Courses->getApplyError());

        $this->Courses->applySettings(
            ['available' => 0, 'apply' => ['available' => true]],
            [2, 3],
            $this->Courses->Students->get(1),
            'teachers_pet'
        );

        $entities = $this->Courses->find('available');
        $this->assertSame(1, $entities->count());
    }

    /**
     * Testing getters and setters on the Check Add Trait
     *
     * @return void
     */
    public function testCheckAddTraitGetAndSet(): void
    {
        $this->assertSame('slug', $this->Courses->getSlugField());
        $this->Courses->setSlugField('slug_other');
        $this->assertSame('slug_other', $this->Courses->getSlugField());

        $this->assertSame('name', $this->Courses->getNameField());
        $this->Courses->setNameField('name_other');
        $this->assertSame('name_other', $this->Courses->getNameField());
    }

    /**
     * Testing checkAdd() on the Check Add Trait
     *
     * @return void
     */
    public function testCheckAddTraitGetCheckAdd(): void
    {
        // an new course, custom slug, and fields, return entity
        $entity = $this->Courses->checkAdd('New Course 4', 'custom slug 4', ['updateme' => 'updateme 4'], true);
        $this->assertSame(4, $entity->get('id'));
        $this->assertSame('New Course 4', $entity->get('name'));
        $this->assertSame('custom slug 4', $entity->get('slug'));
        $this->assertSame('updateme 4', $entity->get('updateme'));

        $result = $this->Courses->getCheckAdd('custom slug 4');
        $this->assertSame('New Course 4', $result->get('name'));

        $this->assertNull($this->Courses->getCheckAdd(''));
    }

    /**
     * Testing checkAdd() on the Check Add Trait
     *
     * @return void
     */
    public function testCheckAddTraitCheckAddCacheOn(): void
    {
        $this->Courses->setUseCache(true);

        $this->runCheckAddTests();
    }

    /**
     * Testing checkAdd() on the Check Add Trait
     *
     * @return void
     */
    public function testCheckAddTraitCheckAddCacheOff(): void
    {
        $this->Courses->setUseCache(false);

        $this->runCheckAddTests();
    }

    /**
     * Keepin it dry
     *
     * @return void
     */
    public function runCheckAddTests(): void
    {
        // add a new course.
        $course_id = $this->Courses->checkAdd('New Course');
        $this->assertSame(4, $course_id);

        // an existing course
        $course_id = $this->Courses->checkAdd('New Course');
        $this->assertSame(4, $course_id);

        // add a new course with a custom slug
        $course_id = $this->Courses->checkAdd('New Course 2', 'custom slug 2');
        $this->assertSame(5, $course_id);
        $entity = $this->Courses->get($course_id);
        $this->assertSame('New Course 2', $entity->get('name'));
        $this->assertSame('custom slug 2', $entity->get('slug'));

        // an existing course with a custom slug
        $course_id = $this->Courses->checkAdd('New Course 2', 'custom slug 2');
        $this->assertSame(5, $course_id);
        $entity = $this->Courses->get($course_id);
        $this->assertSame('New Course 2', $entity->get('name'));
        $this->assertSame('custom slug 2', $entity->get('slug'));

        // an new course, custom slug, and fields
        $course_id = $this->Courses->checkAdd('New Course 3', 'custom slug 3', ['updateme' => 'updateme']);
        $this->assertSame(6, $course_id);
        $entity = $this->Courses->get($course_id);
        $this->assertSame('New Course 3', $entity->get('name'));
        $this->assertSame('custom slug 3', $entity->get('slug'));
        $this->assertSame('updateme', $entity->get('updateme'));

        // an existing course, custom slug, and fields
        $course_id = $this->Courses->checkAdd('New Course 3', 'custom slug 3', ['updateme' => 'updateme']);
        $this->assertSame(6, $course_id);
        $entity = $this->Courses->get($course_id);
        $this->assertSame('New Course 3', $entity->get('name'));
        $this->assertSame('custom slug 3', $entity->get('slug'));
        $this->assertSame('updateme', $entity->get('updateme'));

        // an new course, custom slug, and fields, return entity
        $entity = $this->Courses->checkAdd('New Course 4', 'custom slug 4', ['updateme' => 'updateme 4'], true);
        $this->assertSame(7, $entity->get('id'));
        $this->assertSame('New Course 4', $entity->get('name'));
        $this->assertSame('custom slug 4', $entity->get('slug'));
        $this->assertSame('updateme 4', $entity->get('updateme'));

        // an existing course, custom slug, and fields, return entity
        $entity = $this->Courses->checkAdd('New Course 4', 'custom slug 4', ['updateme' => 'updateme 4'], true);
        $this->assertSame(7, $entity->get('id'));
        $this->assertSame('custom slug 4', $entity->get('slug'));
        $this->assertSame('updateme 4', $entity->get('updateme'));

        // an new course, custom slug, and fields, return entity array with false
        $course_id = $this->Courses->checkAdd('New Course 5', 'custom slug 5', ['updateme' => 'updateme 5'], ['returnEntity' => false]);
        $this->assertSame(8, $course_id);
        $entity = $this->Courses->get($course_id);
        $this->assertSame(8, $entity->get('id'));
        $this->assertSame('New Course 5', $entity->get('name'));
        $this->assertSame('custom slug 5', $entity->get('slug'));
        $this->assertSame('updateme 5', $entity->get('updateme'));

        // an existing course, custom slug, and fields, return entity array with true
        $entity = $this->Courses->checkAdd('New Course 5', 'custom slug 5', ['updateme' => 'updateme 5'], ['returnEntity' => true]);
        $this->assertSame(8, $entity->get('id'));
        $this->assertSame('New Course 5', $entity->get('name'));
        $this->assertSame('custom slug 5', $entity->get('slug'));
        $this->assertSame('updateme 5', $entity->get('updateme'));

        // an existing course, custom slug, and fields, return entity array with true
        $entity = $this->Courses->checkAdd('New Course 5', 'custom slug 5', ['updateme' => 'updateme updated'], ['returnEntity' => true, 'update' => true]);
        $this->assertSame(8, $entity->get('id'));
        $this->assertSame('New Course 5', $entity->get('name'));
        $this->assertSame('custom slug 5', $entity->get('slug'));
        $this->assertSame('updateme updated', $entity->get('updateme'));

        // an existing course, custom slug, and fields, return entity array with true
        $entity = $this->Courses->checkAdd('New Course 5', 'custom slug 9', ['updateme' => 'updateme updated'], ['returnEntity' => true, 'old_slug' => 'custom slug 4']);
        $this->assertSame(7, $entity->get('id'));
        $this->assertSame('New Course 4', $entity->get('name'));
        $this->assertSame('custom slug 4', $entity->get('slug'));
        $this->assertSame('updateme 4', $entity->get('updateme'));

        // an existing course, custom slug, and fields, return entity array with true
        $entity = $this->Courses->checkAdd('New Course 5', 'custom slug 9', ['updateme' => 'updateme updated'], ['returnEntity' => true, 'update' => true, 'old_slug' => 'custom slug 4']);
        $this->assertSame(7, $entity->get('id'));
        $this->assertSame('New Course 5', $entity->get('name'));
        $this->assertSame('custom slug 9', $entity->get('slug'));
        $this->assertSame('updateme updated', $entity->get('updateme'));

        // test tracking of last entity.
        $entity = $this->Courses->getLastEntity();
        $this->assertSame('New Course 5', $entity->get('name'));
        $this->assertSame('custom slug 9', $entity->get('slug'));
        $this->assertSame('updateme updated', $entity->get('updateme'));
    }

    /**
     * Testing fixNameCommon() on the CheckAdd Trait
     *
     * @return void
     */
    public function testCheckAddTraitFixNameCommon(): void
    {
        $name = $this->Courses->fixNameCommon('   New Course 5   ');

        $this->assertSame('New Course 5', $name);
    }

    /**
     * Testing a finder method.
     *
     * @return void
     */
    public function testToggleTrait(): void
    {
        $entity = $this->Courses->get(3);
        $this->assertTrue($entity->get('available'));

        $this->Courses->toggle($entity->get('id'), 'available');

        $entity = $this->Courses->get(3);
        $this->assertFalse($entity->get('available'));

        $this->Courses->toggle($entity->get('id'), 'available');

        $entity = $this->Courses->get(3);
        $this->assertTrue($entity->get('available'));
    }

    /**
     * Testing a finder method.
     *
     * @return void
     */
    public function testSluggableTrait(): void
    {
        $entity = $this->Courses->newEntity([
            'name' => 'Herbology',
        ]);
        $this->Courses->saveOrFail($entity);
        $this->assertTrue($entity->has('slug'));
        $this->assertSame('21ab3a26fe6438ba3de4ec0a1ad91679a02cb78e', $entity->get('slug'));

        $entity = $this->Courses->get(1);
        $this->assertSame(null, $entity->get('slug'));
        $entity->set('name', 'Potions Updated');
        $this->Courses->saveOrFail($entity);
        $this->assertSame('3567d12373aab20a1b9b1a369fe86c2187031e8d', $entity->get('slug'));

        $entity = $this->Courses->get(2);
        $this->assertSame(null, $entity->get('slug'));
        $entity = $this->Courses->sluggableRegenSlug($entity);
        $this->assertSame('510d996e747239c8e90d4fba7b088a7024bd4516', $entity->get('slug'));

        $this->assertSame('', $this->Courses->sluggableSlugify());
        $this->assertSame('', $this->Courses->sluggableSlugify(null));
        $this->assertSame('310b86e0b62b828562fc91c7be5380a992b2786a', $this->Courses->sluggableSlugify(100));
        $this->assertSame('6ae999552a0d2dca14d62e2bc8b764d377b1dd6c', $this->Courses->sluggableSlugify('name'));
        $this->assertSame('6ae999552a0d2dca14d62e2bc8b764d377b1dd6c', $this->Courses->sluggableSlugify([
            'name' => 'name',
        ]));
        $this->assertSame('6ae999552a0d2dca14d62e2bc8b764d377b1dd6c', $this->Courses->sluggableSlugify(new \ArrayObject([
            'name' => 'name',
        ])));
    }
}
