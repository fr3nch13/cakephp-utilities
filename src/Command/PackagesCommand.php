<?php
declare(strict_types=1);

/**
 * PackagesCommand
 *
 * Lists out the package info from the composer.lock file, and git.
 */

namespace Fr3nch13\Utilities\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\View\View;
use Fr3nch13\Utilities\View\Helper\VersionsHelper;

/**
 * Packages Command
 */
class PackagesCommand extends Command
{
    /**
     * @var \Cake\View\View|null
     */
    public $View = null;

    /**
     * @var \Fr3nch13\Utilities\View\Helper\VersionsHelper|null
     */
    public $Versions = null;

    /**
     * Define the options from the command line.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Initial Option Parser
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        // Define your options and arguments.
        $parser->setDescription($this->getDescription());
        $parser->addOption('path', [
            'short' => 'p',
            'help' => __('The directory where the composer.lock file is located.'),
            'default' => getenv('LOCK_DIR') ? getenv('LOCK_DIR') : getenv('ROOT'),
        ]);
        $parser->addOption('command', [
            'short' => 'c',
            'help' => __('Which command to run.'),
            'default' => 'all',
            'choices' => ['all', 'app', 'packages', 'compare'],
        ]);

        // Return the completed parser
        return $parser;
    }

    /**
     * Description for this command.
     * See: https://book.cakephp.org/4/en/console-commands/commands.html#setting-command-description
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return __('Lists out the package info from the composer.lock file, and git.');
    }

    /**
     * Executes the command code
     *
     * @param \Cake\Console\Arguments $args Arguments passed from the command line
     * @param \Cake\Console\ConsoleIo $io IO interface to write to console.
     * @return int
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $path = getenv('LOCK_DIR') ? getenv('LOCK_DIR') : getenv('ROOT');
        if ($args->getOption('path')) {
            $path = $args->getOption('path');
        }
        $this->View = new View();
        $config = ['rootDir' => $path];
        if ($args->getOption('verbose')) {
            $io->verbose(__("Using path:\t{0}", [$path]));
            $io->verbose(__("Using command:\t{0}", [$args->getOption('command')]));
            $io->hr();
        }
        try {
            $this->Versions = new VersionsHelper($this->View, $config);
        } catch (\Throwable $e) {
            $io->abort(__('There was an error when running `{0}`. Error: `{1}`', [
                $args->getOption('command'),
                $e->getMessage(),
            ]));
        }
        // see which command to run.
        switch ($args->getOption('command')) {
            case 'all':
                $this->subAll($args, $io);
                break;
            case 'app':
                $this->subApp($args, $io);
                break;
            case 'packages':
                $this->subPackages($args, $io);
                break;
            case 'compare':
                $this->subCompare($args, $io);
                break;
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Runs all of the commands. defined in cli by -c/--command
     *
     * @param \Cake\Console\Arguments $args Arguments passed from the command line
     * @param \Cake\Console\ConsoleIo $io IO interface to write to console.
     * @return int
     */
    public function subAll(Arguments $args, ConsoleIo $io): int
    {
        $this->subApp($args, $io);
        $this->subPackages($args, $io);
        $this->subCompare($args, $io);

        return static::CODE_SUCCESS;
    }

    /**
     * Gets info about the app from git. defined in cli by -c/--command
     *
     * @param \Cake\Console\Arguments $args Arguments passed from the command line
     * @param \Cake\Console\ConsoleIo $io IO interface to write to console.
     * @return int
     */
    public function subApp(Arguments $args, ConsoleIo $io): int
    {
        $result = $this->Versions->app();
        $io->out(__("App:\t{0}", [$result]));
        $io->hr();

        return static::CODE_SUCCESS;
    }

    /**
     * Gets the version info of all of the installed packages.
     *
     * @param \Cake\Console\Arguments $args Arguments passed from the command line
     * @param \Cake\Console\ConsoleIo $io IO interface to write to console.
     * @return int
     */
    public function subPackages(Arguments $args, ConsoleIo $io): int
    {
        $results = $this->Versions->getPackages(1);
        $io->out(__('Production Packages: {0}', [count($results)]));
        if (count($results)) {
            $table = [];
            $table['0'] = ['Name', 'Version', 'Type'];
            foreach ($results as $result) {
                $name = $result->getName();
                $version = $result->getVersion();
                $type = $result->getType();
                $version = ltrim($version, 'v');
                $table[$name] = [$name, $version, $type];
            }
            ksort($table);
            $io->helper('Table')->output($table);
        }
        $results = $this->Versions->getPackages(2);
        $io->out(__('Development Packages: {0}', [count($results)]));
        if (count($results)) {
            $table = [];
            $table['0'] = ['Name', 'Version', 'Type'];
            foreach ($results as $result) {
                $name = $result->getName();
                $version = $result->getVersion();
                $type = $result->getType();
                $version = ltrim($version, 'v');
                $table[$name] = [$name, $version, $type];
            }
            ksort($table);
            $io->helper('Table')->output($table);
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Compares the currently installed packages from composer.lock to what composer update would install.
     *
     * @param \Cake\Console\Arguments $args Arguments passed from the command line
     * @param \Cake\Console\ConsoleIo $io IO interface to write to console.
     * @return int
     */
    public function subCompare(Arguments $args, ConsoleIo $io): int
    {
        //$result = $this->Versions->app();
        //$io->out(__("App:\t{0}", [$result]));
        //$io->hr();
        $io->out(__('Not implimented yet.'));

        return static::CODE_SUCCESS;
    }
}
