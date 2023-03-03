<?php
declare(strict_types=1);

/**
 * Plugin Definitions
 */

namespace Fr3nch13\Utilities;

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

        $app->addPlugin('Migrations'); // mainly used for testing.

        parent::bootstrap($app);
    }

    /**
     * Add plugin specific routes here.
     *
     * @param \Cake\Routing\RouteBuilder $routes The passed routes object.
     * @return void
     */
    public function routes(RouteBuilder $routes): void
    {
        // Add routes.
        $routes->plugin(
            'Fr3nch13/Utilities',
            ['path' => '/fr3nch13u'],
            function (RouteBuilder $routes) {
                $routes->setExtensions(['json']);
                $routes->fallbacks(DashedRoute::class);
            }
        );

        parent::routes($routes);
    }
}
