<?php
declare(strict_types=1);

/**
 * PackagesTest
 *
 * Tests for the Packages Command.
 */

namespace Fr3nch13\Utilities\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Fr3nch13\Utilities\Command\PackagesCommand;

class PackagesCommandTest extends TestCase
{
    /**
     * Trait to setup the environment for testing commands.
     */
    use ConsoleIntegrationTestTrait;

    /**
     * @var \Fr3nch13\Utilities\Command\PackagesCommand
     */
    protected $command;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->useCommandRunner();

        $lockdir = TESTS . 'assets';
        putenv("LOCK_DIR=$lockdir");
        $this->command = new PackagesCommand();
    }

    /**
     * Test \Fr3nch13\Utilities\Command\PackagesCommand::buildOptionParser()
     *
     * @return void
     */
    public function testBuildOptionParser(): void
    {
        $parser = $this->command->getOptionParser();
        $options = $parser->options();

        $this->assertArrayHasKey('command', $options);
        $choices = $options['command']->choices();
        $this->assertEquals(4, count($choices));
        $this->assertEquals('c', $options['command']->short());

        $this->assertArrayHasKey('path', $options);
        $this->assertEquals('p', $options['path']->short());
    }

    /**
     * Test \Fr3nch13\Utilities\Command\PackagesCommand::getDescription()
     *
     * @return void
     */
    public function testGetDescription(): void
    {
        $description = $this->command->getDescription();
        $this->assertEquals(true, is_string($description));
    }

    /**
     * Test the help option.
     *
     * @return void
     */
    public function testHelpOption(): void
    {
        $this->exec('packages -h');
        $this->assertExitCode(\Cake\Command\Command::CODE_SUCCESS);
        $this->assertOutputContains('Lists out the package info from the composer.lock file, and git.');
        $this->assertOutputContains('cake packages [-c all|app|packages|compare]');
        $this->assertOutputContains('--command, -c  Which command to run.');
        $this->assertOutputContains('--path, -p     The directory where the composer.lock file is located.');
    }

    /**
     * Test the verbose option.
     *
     * @return void
     */
    public function testVerboseOption(): void
    {
        $this->exec('packages -v');
        $this->assertExitCode(\Cake\Command\Command::CODE_SUCCESS);
    }

    /**
     * Test no options.
     *
     * @return void
     */
    public function testNoOptions(): void
    {
        $this->exec('packages');
        $this->assertExitCode(\Cake\Command\Command::CODE_SUCCESS);
        $this->assertOutputRegExp('/Production Packages\: \d+/');
        $this->assertOutputRegExp('/\|\s+cakephp\/cakephp\s+\|\s+[\d\.]+\s+\|\s+library\s+\|/');
        $this->assertOutputRegExp('/Development Packages\: \d+/');
        $this->assertOutputRegExp('/\|\s+phpunit\/phpunit\s+\|\s+[\d\.]+\s+\|\s+library\s+\|/');
    }

    /**
     * Test the path options.
     *
     * @return void
     */
    public function testOptionPath(): void
    {
        $this->exec('packages -p ./');
        $this->assertExitCode(\Cake\Command\Command::CODE_SUCCESS);
        $this->assertOutputRegExp('/Production Packages\: \d+/');
        $this->assertOutputRegExp('/\|\s+cakephp\/cakephp\s+\|\s+[\d\.]+\s+\|\s+library\s+\|/');
        $this->assertOutputRegExp('/Development Packages\: \d+/');
        $this->assertOutputRegExp('/\|\s+phpunit\/phpunit\s+\|\s+[\d\.]+\s+\|\s+library\s+\|/');
    }

    /**
     * Test when the path option is bad.
     *
     * @return void
     */
    public function testOptionBadPath(): void
    {
        $this->exec('packages -p /does/not/exist');
        $this->assertExitCode(\Cake\Command\Command::CODE_ERROR);
        $this->assertErrorContains('There was an error when running `all`. Error: `Cannot find the root of the application, unable to get versions.`');
    }

    /**
     * Test the command option with all selected.
     *
     * @return void
     */
    public function testOptionCommandAll(): void
    {
        $this->exec('packages -c all');
        $this->assertExitCode(\Cake\Command\Command::CODE_SUCCESS);
        $this->assertOutputRegExp('/Production Packages\: \d+/');
        $this->assertOutputRegExp('/\|\s+cakephp\/cakephp\s+\|\s+[\d\.]+\s+\|\s+library\s+\|/');
        $this->assertOutputRegExp('/Development Packages\: \d+/');
        $this->assertOutputRegExp('/\|\s+phpunit\/phpunit\s+\|\s+[\d\.]+\s+\|\s+library\s+\|/');
    }

    /**
     * Test the command option with app selected.
     *
     * @return void
     */
    public function testOptionCommandApp(): void
    {
        $this->exec('packages -c app');
        $this->assertExitCode(\Cake\Command\Command::CODE_SUCCESS);
        $this->assertOutputRegExp('/App\:\s+\w+/');
    }

    /**
     * Test the command option with packages selected.
     *
     * @return void
     */
    public function testOptionCommandPackages()
    {
        $this->exec('packages -c packages');
        $this->assertExitCode(\Cake\Command\Command::CODE_SUCCESS);
        $this->assertOutputRegExp('/Production Packages\: \d+/');
        $this->assertOutputRegExp('/\|\s+cakephp\/cakephp\s+\|\s+[\d\.]+\s+\|\s+library\s+\|/');
        $this->assertOutputRegExp('/Development Packages\: \d+/');
        $this->assertOutputRegExp('/\|\s+phpunit\/phpunit\s+\|\s+[\d\.]+\s+\|\s+library\s+\|/');
    }

    /**
     * Test the command option with compare selected.
     *
     * @return void
     */
    public function testOptionCommandCompare(): void
    {
        $this->exec('packages -c compare');
        $this->assertExitCode(\Cake\Command\Command::CODE_SUCCESS);
        $this->assertOutputContains('Not implimented yet.');
    }
}
