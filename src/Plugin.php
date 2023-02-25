<?php
declare(strict_types=1);

/**
 * Plugin Definitions
 */

namespace Fr3nch13\Utilities;

use Cake\Cache\Cache;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/**
 * Plugin Definitions
 */
class Plugin extends BasePlugin
{
    /**
     * Bootstraping for this specific plugin.
     *
     * @param \Cake\Core\PluginApplicationInterface $app The app object.
     * @return void
     */
    public function bootstrap(\Cake\Core\PluginApplicationInterface $app): void
    {
        // Add constants, load configuration defaults.
        if (!Configure::read('Utilities')) {
            Configure::write('Utilities', [
                'test' => 'TEST',
            ]);
        }

        // By default will load `config/bootstrap.php` in the plugin.
        parent::bootstrap($app);
    }
}
