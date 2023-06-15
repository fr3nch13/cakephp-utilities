<?php
declare(strict_types=1);

/**
 * Versions Helper
 *
 * Helper to read the versions of vendors/plugins for the parent app.
 */

namespace Fr3nch13\Utilities\View\Helper;

use Cake\View\Helper;
use Cake\View\View;
use ComposerLockParser\ComposerInfo;
use ComposerLockParser\PackagesCollection;
use Fr3nch13\Utilities\Exception\UtilitiesException;

/**
 * Versions Helper
 *
 * Helper to read the versions of vendors/plugins for the parent app.
 *
 * Uses the Composer Lock Parser to get detailed version onformation from the composer.lock file.
 * It also uses git to try to determine the version of the application itself.
 *
 * @property \Cake\View\Helper\UrlHelper $Url
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class VersionsHelper extends Helper
{
    /**
     * List of loaded helpers.
     *
     * @var array<int|string, mixed>
     */
    public $helpers = ['Url', 'Html'];

    /**
     * Contains the loaded composer info.
     *
     * @var \ComposerLockParser\ComposerInfo|null
     */
    protected $ComposerInfo = null;

    /**
     * The path to the composer.lock file to parse/use.
     *
     * @var string
     */
    protected $composerPath = '';

    /**
     * The path to the git command.
     *
     * @var null|string
     */
    protected $gitCmd = null;

    /**
     * The root directory of the application
     */
    protected $rootDir = '';

    /**
     * Initialize the helper
     *
     * @param \Cake\View\View $View The view object
     * @param array<string, mixed> $config Helper config settings
     * @return void
     * @throws UtilitiesException when we can't find some of the commands.
     */
    public function __construct(View $View, array $config = [])
    {
        parent::__construct($View, $config);
        // get the git command
        $output = [];
        if (isset($config['git'])) {
            $this->gitCmd = $config['git'];
        } else {
            try {
                exec('which git', $output);
            } catch (\Throwable $e) {
                throw new UtilitiesException(__('Unable to find the `which` command.'));
            }
            if (isset($output[0])) {
                $this->gitCmd = $output[0];
            } else {
                throw new UtilitiesException(__('Unable to find the `git` command.'));
            }
        }
        // use an environment vairable if set like in config/.env
        // otherwise use the constant ROOT from the source application
        $this->rootDir = getenv('LOCK_DIR') ?: (getenv('ROOT') ?: ROOT);
        if (isset($config['rootDir'])) {
            $this->rootDir = $config['rootDir'];
        }

        $this->composerPath = $this->getRootDir() . DS . 'composer.lock';
        if (isset($config['composerPath'])) {
            $this->composerPath = $config['composerPath'];
        }

        if (!is_file($this->composerPath)) {
            throw new UtilitiesException(__('Unable to find the composer.lock file at: {0}', [
                $this->composerPath,
            ]));
        }
        try {
            $this->ComposerInfo = new ComposerInfo($this->composerPath);
            $this->ComposerInfo->getPackages();
        } catch (\Throwable $e) {
            throw new UtilitiesException(__('Unable to parse the composer.lock file at: {0} Msg: {1}', [
                $this->composerPath,
                $e->getMessage(),
            ]));
        }
    }

    /**
     * Gets the version of this app using git
     *
     * @return string|null The version according to git
     */
    public function app(): ?string
    {
        // see if we're running on an actual branch
        $out = null;
        $results = $this->runGit(['branch']);
        foreach ($results as $line) {
            if (substr($line, 0, 1) === '*') {
                $out = trim($line, ' *');
                break;
            }
        }
        if ($out) {
            $matches = [];
            if (preg_match('/^\(HEAD detached at ([\/\w]+)\)$/', $out, $matches)) {
                $out = $matches[1];
            }
            $out = 'dev-' . $out;
        }
        // most likely we're on a tag/version
        if (stripos($out, 'detached') !== false) {
            try {
                $results = $this->runGit(['describe', '--tags']);
                if (isset($results[0])) {
                    $out = $results[0];
                }
            } catch (UtilitiesException $e) {
                // Don't throw an exception, just return the output of the command.
            }
        }

        return $out;
    }

    /**
     * Gets info on a particular package
     *
     * @param string $name The full name of the composer package ex: fr3nch13/utilities
     * @return object the object that has the info of that package
     * @throws UtilitiesException throws an exception if the package info can't be found.
     * @TODO Use more specific exceptions
     */
    public function package(string $name): object
    {
        try {
            return $this->ComposerInfo->getPackages()->getByName($name);
        } catch (\Throwable $e) {
            throw new UtilitiesException(__('Unable to find info on {0}.', [
                $name,
            ]));
        }
    }

    /**
     * Gets a list of all available packages
     *
     * @param int $list What list of packages should we return.
     *      0 - Both dev and production.
     *      1 - Just production.
     *      2 - Just dev.
     * @param null|string $type If given, only packages of this type are returned
     * @return \ComposerLockParser\PackagesCollection<\ComposerLockParser\Package> a list of package objects
     */
    public function getPackages(int $list = 0, ?string $type = null): PackagesCollection
    {
        $_packages = $this->ComposerInfo->getPackages($list);
        if ($type) {
            $packages = new PackagesCollection();
            foreach ($_packages as $i => $package) {
                if ($package->getType() == $type) {
                    $packages->append($package);
                }
            }
        } else {
            $packages = $_packages;
        }

        return $packages;
    }

    /**
     * Returns the list of available package types
     *
     * @return array<int|string, mixed> the list of found/available packages
     */
    public function getTypes(): array
    {
        $types = [];
        $packages = $this->ComposerInfo->getPackages();
        foreach ($packages as $package) {
            $type = $package->getType();
            $types[$type] = $type;
        }

        return $types;
    }

    /**
     * Gets the root path of the application.
     *
     * @return string The path to the root folder
     * @throws UtilitiesException If we can't find cakephp as this means nothing is installed yet.
     */
    public function getRootDir(): string
    {
        // it's already bee set, so just return the defined path
        if ($this->rootDir)
        {
            return $this->rootDir;
        }

        $findRoot = function ($root) {
            if (is_dir($root . '/vendor/cakephp/cakephp')) {
                return $root;
            }
            do {
                $lastRoot = $root;
                $root = dirname($root);
                if (is_dir($root . '/vendor/cakephp/cakephp')) {
                    return $root;
                }
            } while ($root !== $lastRoot);
            throw new UtilitiesException(
                "Cannot find the root of the application, unable to run tests"
            );
        };

        $this->rootDir = $findRoot(getcwd());
        return $this->rootDir;
    }

    /**
     * Runs the git command with the args
     *
     * @param array<string> $args List of arguments to pass to the git command
     * @return array<int, string> The result of the git command
     * @throws UtilitiesException if the git command fails.
     * @TODO Use more specific exceptions
     */
    public function runGit(array $args = []): array
    {
        $cmd = 'cd ' . $this->getRootDir() . '; ';
        $cmd .= $this->gitCmd . ' ' . implode(' ', $args);
        $output = [];
        try {
            exec($cmd, $output, $result_code);
        } catch (\Throwable $e) {
            throw new UtilitiesException(__('Unable to find the `which` command.'));
        }
        if ($result_code) {
            /** @var string $msg */
            $msg = json_encode([
                'message' => 'Command failed',
                'cmd' => '{0}',
                'code' => '{1}',
                'output' => '{2}',
            ]);
            throw new UtilitiesException(__($msg, [
                $cmd,
                $result_code,
                implode("\n", $output),
            ]));
        }
        // trim the results
        foreach ($output as $i => $value) {
            $output[$i] = trim($value);
        }

        return $output;
    }
}
