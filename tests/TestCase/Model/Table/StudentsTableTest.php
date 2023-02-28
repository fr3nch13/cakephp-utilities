<?php
declare(strict_types=1);

/**
 * StudentsTableTest
 */

namespace Fr3nch13\Utilities\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;

/**
 * StudentsTable Test
 */
class StudentsTableTest extends TestCase
{
    /**
     * @var \Fr3nch13\Utilities\Model\Table\StudentsTable The table object.
     */
    public $Students;

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

        /** @var \Fr3nch13\Utilities\Model\Table\StudentsTable $Students */
        $Students = $Locator->get('Fr3nch13/Utilities.Students');
        $this->Students = $Students;
    }

    /**
     * Tests the class name of the Table
     *
     * @return void
     */
    public function testClassInstance(): void
    {
        $this->assertInstanceOf(\Fr3nch13\Utilities\Model\Table\StudentsTable::class, $this->Students);
    }

    /**
     * Testing a method.
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertEquals('students', $this->Students->getTable());
        $this->assertEquals('name', $this->Students->getDisplayField());
        $this->assertEquals('id', $this->Students->getPrimaryKey());
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
        ];
        foreach ($behaviors as $name => $class) {
            $behavior = $this->Students->behaviors()->get($name);
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
        $Associations = $this->Students->associations();

        // make sure the association exists
        $this->assertNotNull($Associations->get('Books'));
        $this->assertInstanceOf(\Cake\ORM\Association\HasMany::class, $Associations->get('Books'));
        $Association = $Associations->get('Books');
        $this->assertSame('Books', $Association->getName());
        $this->assertSame('student_id', $Association->getForeignKey());
        $this->assertSame('Fr3nch13/Utilities.Books', $Association->getClassName());
    }

    /**
     * Test the entity itself
     *
     * @return void
     */
    public function testEntity(): void
    {
        $entity = $this->Students->get(1, [
            'contain' => [
                'Books',
            ],
        ]);

        $this->assertSame('Harry', $entity->get('name'));
    }

    /**
     * Test the apply settting trait itself
     *
     * @return void
     */
    public function testMergeDeleteTraitMergeDelete(): void
    {
        $Harry = $this->Students->get(1, [
            'contain' => [
                'Books',
                'Courses',
            ],
        ]);

        $Ron = $this->Students->get(2, [
            'contain' => [
                'Books',
                'Courses',
            ],
        ]);

        $this->assertSame('Harry', $Harry->get('name'));
        $this->assertSame(7, count($Harry->get('books')));
        $this->assertSame(3, count($Harry->get('courses'))); // Harry is in 3

        $this->assertSame('Ron', $Ron->get('name'));
        $this->assertSame(0, count($Ron->get('books')));
        $this->assertSame(2, count($Ron->get('courses'))); // Ron is only in 2

        // Ron gets all of Harry's books, and any courses that he's not already in.
        $this->Students->mergeDelete(1, 2);

        $Ron = $this->Students->get(2, [
            'contain' => [
                'Books',
                'Courses',
            ],
        ]);

        $this->assertSame('Ron', $Ron->get('name'));
        $this->assertSame(7, count($Ron->get('books'))); // He was given harry's books.
        $this->assertSame(5, count($Ron->get('courses'))); // Ron should have 5 course entries now, because he was given Harry's
    }

    /**
     * Test the apply settting trait itself
     *
     * @return void
     */
    public function testMergeDeleteTraitFindMergeRecords(): void
    {
        $records = $this->Students->find('mergeRecords', [
            'sourceId' => 1,
        ]);
        $this->assertSame(2, $records->count());
        $this->assertSame([
            2 => 'Ron',
            3 => 'Hermione',
        ], $records->toArray());

        $records = $this->Students->find('mergeRecords', [
            'sourceId' => 'Harry',
            'primaryKey' => 'name',
            'displayField' => 'slug',
        ]);

        $this->assertSame(2, $records->count());
        $this->assertSame([
            'Ron' => 'slug-2',
            'Hermione' => 'slug-3',
        ], $records->toArray());
    }

    /**
     * Test the apply settting trait itself
     *
     * @return void
     */
    public function testMergeDeleteTraitGetMergeStats(): void
    {
        $stats = $this->Students->getMergeStats(1);
        $this->assertSame([
            'Books' => [
                'name' => 'Books',
                'count' => 7,
              ],
              'Courses' => [
                'name' => 'Courses',
                'count' => 3,
              ],
        ], $stats);
    }

    /**
     * Test the memory behavior
     *
     * @return void
     */
    public function testMemoryBehavior(): void
    {
        $usage = $this->Students->memoryUsage(false, 100);
        $this->assertSame('100', $usage);

        $usage = $this->Students->memoryUsage(true, 100);
        $this->assertSame('100 B', $usage);

        $usage = $this->Students->memoryUsage(false, 1024);
        $this->assertSame('1024', $usage);

        $usage = $this->Students->memoryUsage(true, 1024);
        $this->assertSame('1 KB', $usage);

        $usage = $this->Students->memoryUsageHighest(false);
        $this->assertSame('1024', $usage);

        $usage = $this->Students->memoryUsageHighest(true);
        $this->assertSame('1 KB', $usage);

        $usage = $this->Students->memoryUsage(true, 1024 * 1024);
        $this->assertSame('1 MB', $usage);

        $usage = $this->Students->memoryUsageHighest(false);
        $this->assertSame('1048576', $usage);

        $usage = $this->Students->memoryUsageHighest(true);
        $this->assertSame('1 MB', $usage);

        $usage = $this->Students->memoryUsage(true, 1024 * 1024 * 1024);
        $this->assertSame('1 GB', $usage);

        $usage = $this->Students->memoryUsageHighest(false);
        $this->assertSame('1073741824', $usage);

        $usage = $this->Students->memoryUsageHighest(true);
        $this->assertSame('1 GB', $usage);

        $usage = $this->Students->memoryUsage(false, 1024);

        $usage = $this->Students->memoryUsageHighest(false);
        $this->assertSame('1073741824', $usage);

        $usage = $this->Students->memoryUsageHighest(true);
        $this->assertSame('1 GB', $usage);

        $usage = $this->Students->memoryUsage();
        $this->assertGreaterThan(2, $usage);

        $usage = $this->Students->memoryUsage(true);
        $this->assertIsString($usage);
    }

    /**
     * Tests if the fixName method doesn't exist in the CheckAddTrait
     *
     * @return void
     */
    public function testCheckAddTraitExceptionCheckAdd(): void
    {
        $this->expectException(\Fr3nch13\Utilities\Exception\MissingMethodException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Missing the `Fr3nch13\Utilities\Model\Table\StudentsTable::fixName()` method.');
        $this->Students->checkAdd('New Guy');
    }

    /**
     * Tests if the fixName method doesn't exist in the CheckAddTrait
     *
     * @return void
     */
    public function testCheckAddTraitExceptionSlugify(): void
    {
        $this->expectException(\Fr3nch13\Utilities\Exception\MissingMethodException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Missing the `Fr3nch13\Utilities\Model\Table\StudentsTable::sluggableSlugify()` method.');
        $this->Students->slugify('New Guy');
    }
}
