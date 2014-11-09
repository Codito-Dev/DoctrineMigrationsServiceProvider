<?php

namespace Codito\Silex\DoctrineMigrationsService\Provider;

use Codito\Silex\DoctrineMigrationsService\Console\Command as DoctrineCommands;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Knp\Console\Application as ConsoleApp;
use Knp\Console\ConsoleEvents;
use Knp\Console\ConsoleEvent;
use Doctrine\DBAL\Connection;

/**
 * DoctrineMigrationsServiceProvider determines database connections configuration,
 * prepares initial migrations configuration and registers migrations commands in {@see Knp\Console\Application},
 * which should be registered in main Silex application.
 * 
 * @author Grzegorz Korba <grzegorz.korba@codito.net>
 */
class DoctrineMigrationsServiceProvider implements ServiceProviderInterface {
	// DoctrineServiceProvider's default connection's name (@see 'dbs.options.initializer')
	const DEFAULT_CONNECTION_NAME = 'default';

	/**
	 * Registers provider
	 * @param Application $app
	 */
	public function register(Application $app) {
		// Prototype for migrations config (connection is not configurable, it's retrieved from app config and appended to migrations config)
		$app['db.migrations.config._proto'] = $app->protect(function (Connection $connection, array $config) {
			$defaults = array(
				'dir_name' => null,
				'namespace' => 'Application\\Migrations',
				'table_name' => 'migration_versions',
				'name' => 'Application Migrations',
			);

			return array_merge(
				array_replace($defaults, $config),
				['connection' => $connection]
			);
		});

		// Resolves initial migrations configuration for named connection
		$app['db.migrations.config.resolver'] = $app->protect(function ($name) use($app) {
			if(!isset($app['db.migrations.options']) || !is_array($app['db.migrations.options'])) {
				return [];
			}

			// Handle multiple connections configuration (like 'dbs.options')
			if(isset($app['db.migrations.options'][$name]) && is_array($app['db.migrations.options'][$name])) {
				$config = $app['db.migrations.options'][$name];
			}
			// Handle single connection (like 'db.option')
			elseif($name == self::DEFAULT_CONNECTION_NAME) {
				$config = $app['db.migrations.options'];
			}

			return isset($config) ? $config : [];
		});

		// Main migrations config container
		$app['db.migrations'] = $app->share(function ($app) {
			$migrations = new \Pimple();

			$dbs = $app['dbs']->keys();
			foreach($dbs as $name) {
				$connection = $app['dbs'][$name];

				$migrations[$name] = $app->share(function () use($app, $name, $connection) {
					$dbMigrationConfig = $app['db.migrations.config.resolver']($name);

					return $app['db.migrations.config._proto']($connection, $dbMigrationConfig);
				});
			}

			return $migrations;
		});
	}

	public function boot(Application $app) {
		// Listen for console initialization and add migrations commands
		$app['dispatcher']->addListener(ConsoleEvents::INIT, function(ConsoleEvent $event) {
			$consoleApp = $event->getApplication(); /* @var $console ConsoleApp */
			$consoleApp->add(new DoctrineCommands\ExecuteCommand());
			$consoleApp->add(new DoctrineCommands\MigrateCommand());
			$consoleApp->add(new DoctrineCommands\StatusCommand());
			$consoleApp->add(new DoctrineCommands\VersionCommand());
			$consoleApp->add(new DoctrineCommands\LatestCommand());
			$consoleApp->add(new DoctrineCommands\GenerateCommand());
		});
	}
}