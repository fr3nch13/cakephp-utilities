<?php
declare(strict_types=1);

/**
 * Based on:
 * Gravatar Helper (https://github.com/PotatoPowered/gravatar-helper)
 */

namespace Fr3nch13\Utilities\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use Fr3nch13\Utilities\View\Helper\GravatarHelper;

/**
 * GravatarHelper Test Class
 *
 * This class contains the main tests for the GravatarHelper Class.
 */
class GravatarHelperTest extends TestCase
{
    /**
     * @var \Fr3nch13\Utilities\View\Helper\GravatarHelper
     */
    public $Gravatar;

    /**
     * Setup the application so that we can run the tests.
     *
     * The setup involves initializing a new CakePHP view and using that to
     * get a copy of the GravatarHelper.
     */
    public function setUp(): void
    {
        parent::setUp();
        $View = new View();
        $this->Gravatar = new GravatarHelper($View);
    }

    /**
     * Test the avatar html
     *
     * @return void
     */
    public function testUrl(): void
    {
        $testUrl = $this->Gravatar->url('test@email.com');
        $this->assertEquals('https://www.gravatar.com/avatar/93942e96f5acd83e2e047ad8fe03114d?&s=150&d=mp', $testUrl);

        $testUrl = $this->Gravatar->url('test@email.com', ['size' => '75']);
        $this->assertEquals('https://www.gravatar.com/avatar/93942e96f5acd83e2e047ad8fe03114d?&s=75&d=mp', $testUrl);

        $testUrl = $this->Gravatar->url('test@email.com', ['size' => '75', 'default' => 'robohash']);
        $this->assertEquals('https://www.gravatar.com/avatar/93942e96f5acd83e2e047ad8fe03114d?&s=75&d=robohash', $testUrl);
    }

    /**
     * Test the avatar url.
     *
     * @return void
     */
    public function testAvatar(): void
    {
        $testHtml = $this->Gravatar->avatar('test@email.com');
        $this->assertEquals('<img alt=" Avatar for test@email.com" class="gravatar" src="https://www.gravatar.com/avatar/93942e96f5acd83e2e047ad8fe03114d?&s=150&d=mp"/>', $testHtml);

        $testHtml = $this->Gravatar->avatar('test@email.com', ['class' => 'test-class']);
        $this->assertEquals('<img alt=" Avatar for test@email.com" class="test-class" src="https://www.gravatar.com/avatar/93942e96f5acd83e2e047ad8fe03114d?&s=150&d=mp"/>', $testHtml);
    }
}
