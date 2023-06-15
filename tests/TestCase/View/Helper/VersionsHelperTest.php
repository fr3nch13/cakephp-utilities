<?php
declare(strict_types=1);

namespace Fr3nch13\Utilities\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
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

    public function testConstructorBadPath(): void
    {
        $this->expectException(\Exception::class);
        $versions = new VersionsHelper($this->View, [
            'rootDir' => '/',
        ]);
    }

    public function testConstructorBadLockFile(): void
    {
        $this->expectException(\Exception::class);
        $config = ['rootDir' => dirname(dirname(dirname(__DIR__))) . DS . 'assets'];
        $versions = new VersionsHelper($this->View, [
            'composerPath' => $config['rootDir'] . DS . 'composer_bad.lock',
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

    public function testRunGitBad(): void
    {
        $this->expectException(\Exception::class);
        $versions = new VersionsHelper($this->View, [
            'git' => '/bad/path/to/git',
        ]);
        $results = $versions->runGit(['branch']);
    }

    public function testApp(): void
    {
        $result = $this->Versions->app();
        // dev-(1\.x-dev|master|\w+) - Testing on an actual branch, or head
        // [\d\.-]+\-[a-z0-9]+ - Testing on a Merge Request
        // [\d\.-]+ - Testing when a tag is created.
        // dev-(HEAD detached at pull/1/merge)
        $this->assertMatchesRegularExpression('/^(dev-(1\.x-dev|2\.x-dev|master|[\w\-\/\s+]+)|[\d\.-]+\-[a-z0-9]+|[\d\.-]+)$/i', $result);
        $this->assertStringContainsString('fr3nch13/cakephp-utilities/tests/assets', $this->Versions->getRootDir());
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
        $result = $this->Versions->package('t4web/composer-lock-parser');
        $this->assertInstanceOf('\ComposerLockParser\Package', $result);
        $this->assertEquals('t4web/composer-lock-parser', $result->getName());
        $this->assertEquals('library', $result->getType());

        $this->expectException(\Exception::class);
        $result = $this->Versions->package('dont/exist');
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
}
