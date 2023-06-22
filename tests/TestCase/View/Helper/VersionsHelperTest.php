<?php
declare(strict_types=1);

namespace Fr3nch13\Utilities\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use Fr3nch13\Utilities\Exception\UtilitiesException;
use Fr3nch13\Utilities\View\Helper\VersionsHelper;

class VersionsHelperTest extends TestCase
{
    /**
     * @var \Cake\View\View
     */
    public $View;

    /**
     * @var \Fr3nch13\Utilities\View\Helper\VersionsHelper
     */
    public $Versions;

    public function setUp(): void
    {
        parent::setUp();
        $this->View = new View();
        $config = ['rootDir' => dirname(dirname(dirname(__DIR__))) . DS . 'assets'];
        $this->Versions = new VersionsHelper($this->View, $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->View);
        unset($this->Versions);
        parent::tearDown();
    }

    public function testConstructorNoWhich(): void
    {
        $this->expectException(UtilitiesException::class);
        $this->expectExceptionMessage('Unable to find the `git` command.');
        $this->expectExceptionCode(404);
        $mock = $this->getMockBuilder(VersionsHelper::class)
            ->setConstructorArgs([$this->View])
            ->onlyMethods(['exec'])
            ->getMock();
        $mock->expects($this->once())
            ->method('exec')
            ->willReturnCallback(
                function ($command, &$output, &$return_var) {
                    $this->assertEquals('which git', $command);
                    $output = ['failure'];
                    $return_var = 1;

                    return false;
                }
            );
        new VersionsHelper($this->View);
    }

    public function testConstructorBadPath(): void
    {
        $this->expectException(UtilitiesException::class);
        $this->expectExceptionMessage('Cannot find the root of the application, unable to get versions.');
        $this->expectExceptionCode(404);
        new VersionsHelper($this->View, [
            'rootDir' => '/',
        ]);
    }

    public function testConstructorBadLockFile(): void
    {
        $this->expectException(UtilitiesException::class);
        $this->expectExceptionMessage('Unable to parse the composer.lock');
        $this->expectExceptionCode(500);
        $config = ['rootDir' => dirname(dirname(dirname(__DIR__))) . DS . 'assets'];
        new VersionsHelper($this->View, [
            'composerPath' => $config['rootDir'] . DS . 'composer_bad.lock',
        ]);
    }

    public function testConstructorMissingLockFile(): void
    {
        $this->expectException(UtilitiesException::class);
        $this->expectExceptionMessage('Unable to find the composer.lock');
        $this->expectExceptionCode(404);
        $config = ['rootDir' => dirname(dirname(dirname(__DIR__))) . DS . 'assets'];
        new VersionsHelper($this->View, [
            'composerPath' => $config['rootDir'] . DS . 'doesntexist.lock',
        ]);
    }

    public function testRunGit(): void
    {
        $results = $this->Versions->runGit(['branch']);
        if (count($results) == 1) {
            $this->assertMatchesRegularExpression('/^\*\s+([\(\)\w\s\/]+)$/i', $results[0]);
        } else {
            $result = '';
            foreach ($results as $i => $line) {
                if (substr($results[$i], 0, 1) == '*') {
                    $result = $results[$i];
                    break;
                }
            }
            $this-> assertStringContainsString('*', $result);
        }
    }

    public function testRunGitWithAddSafe(): void
    {
        $results = $this->Versions->runGit(['branch'], true);
        if (count($results) == 1) {
            $this->assertMatchesRegularExpression('/^\*\s+([\(\)\w\s\/]+)$/i', $results[0]);
        } else {
            $result = '';
            foreach ($results as $i => $line) {
                if (substr($results[$i], 0, 1) == '*') {
                    $result = $results[$i];
                    break;
                }
            }
            $this-> assertStringContainsString('*', $result);
        }
    }

    public function testRunGitBad(): void
    {
        $this->expectException(UtilitiesException::class);
        $this->expectExceptionMessage('{"message":"Command failed","cmd":"cd ');
        $this->expectExceptionCode(127);
        $versions = new VersionsHelper($this->View, [
            'git' => '/bad/path/to/git',
        ]);
        $versions->runGit(['branch']);
    }

    public function testRunGitEmpty(): void
    {
        $this->expectException(UtilitiesException::class);
        $this->expectExceptionMessage('Empty git command.');
        $this->expectExceptionCode(404);
        $versions = new VersionsHelper($this->View, [
            'git' => '',
        ]);
        $versions->runGit(['branch']);
    }

    public function testRunGitEmptyNoArgs(): void
    {
        $this->expectException(UtilitiesException::class);
        $this->expectExceptionMessage('Empty git command.');
        $this->expectExceptionCode(404);
        $versions = new VersionsHelper($this->View, [
            'git' => '',
        ]);
        $versions->runGit();
    }

    public function testApp(): void
    {
        $result = $this->Versions->app();
        // dev-(1\.x-dev|master|\w+) - Testing on an actual branch, or head
        // [\d\.-]+\-[a-z0-9]+ - Testing on a Merge Request
        // [\d\.-]+ - Testing when a tag is created.
        // dev-(HEAD detached at pull/1/merge)
        $this->assertMatchesRegularExpression('/^(dev-(1\.x-dev|2\.x-dev|master|[\w\-\/\s+]+)|[\d\.-]+\-[a-z0-9]+|[\d\.-]+)$/i', $result);
        $this->assertStringContainsString('cakephp-utilities/tests/assets', $this->Versions->getRootDir());
    }

    public function testAppBadGit(): void
    {
        $this->expectException(UtilitiesException::class);
        $this->expectExceptionMessage('Empty git command.');
        $this->expectExceptionCode(404);
        $versions = new VersionsHelper($this->View, [
            'git' => '',
        ]);
        $result = $versions->app();
    }

    public function testGetPackages(): void
    {
        $result = $this->Versions->getPackages();
        $this->assertInstanceOf('\ComposerLockParser\PackagesCollection', $result);
        $this->assertEquals(96, count($result));
        $result = $this->Versions->getPackages(0);
        $this->assertInstanceOf('\ComposerLockParser\PackagesCollection', $result);
        $this->assertEquals(96, count($result));
        $result = $this->Versions->getPackages(1);
        $this->assertInstanceOf('\ComposerLockParser\PackagesCollection', $result);
        $this->assertEquals(15, count($result));
        $result = $this->Versions->getPackages(2);
        $this->assertInstanceOf('\ComposerLockParser\PackagesCollection', $result);
        $this->assertEquals(81, count($result));
        $result = $this->Versions->getPackages(0, 'library');
        $this->assertInstanceOf('\ComposerLockParser\PackagesCollection', $result);
        $this->assertEquals(85, count($result));
        $result = $this->Versions->getPackages(0, 'cakephp-plugin');
        $this->assertInstanceOf('\ComposerLockParser\PackagesCollection', $result);
        $this->assertEquals(6, count($result));
    }

    public function testPackage(): void
    {
        /** @var \ComposerLockParser\Package $result */
        $result = $this->Versions->package('fr3nch13/composer-lock-parser');
        $this->assertInstanceOf('\ComposerLockParser\Package', $result);
        $this->assertEquals('fr3nch13/composer-lock-parser', $result->getName());
        $this->assertEquals('library', $result->getType());

        $this->expectException(UtilitiesException::class);
        $this->expectExceptionMessage('Unable to find info on dont/exist.');
        $this->expectExceptionCode(404);
        $this->Versions->package('dont/exist');
    }

    public function testGetTypes(): void
    {
        $results = $this->Versions->getTypes();
        $this->assertEquals(true, is_array($results));
        $this->assertEquals(4, count($results));
        $result = '';
        foreach ($results as $i => $line) {
            if ($results[$i] == 'cakephp-plugin') {
                $result = $results[$i];
                break;
            }
        }
        $this-> assertStringContainsString('cakephp-plugin', $result);
    }

    public function testGetRootDir(): void
    {
        $this->Versions = new VersionsHelper($this->View);

        $this->Versions->rootDir = '';
        $root = $this->Versions->getRootDir();
        $this->assertStringContainsString('cakephp-utilities', $root);

        $this->Versions->rootDir = '.';
        $root = $this->Versions->getRootDir();
        $this->assertStringContainsString('cakephp-utilities', $root);

        $this->Versions->rootDir = '.' . DS . 'vendor';
        $root = $this->Versions->getRootDir();
        $this->assertStringContainsString('cakephp-utilities', $root);
    }
}
