<?php

namespace Codito\Silex\DoctrineMigrationsService\Provider;

use Codito\Silex\DoctrineMigrationsService\Console\Command as DoctrineCommands;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Knp\Console\Application as ConsoleApp;
use Knp\Console\ConsoleEvents;
use Knp\Console\ConsoleEvent;
use Doctrine\DBAL\Connection;

class DoctrineMigrationsServiceProvider implements ServiceProviderInterface {
	// DoctrineServiceProvider's default connection's name (@see 'dbs.options.initializer')
	const DEFAULT_CONNECTION_NAME = 'default';

	public function register(Application $app) {
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

		$app['db.migrations.config.resolver'] = $app->protect(function ($name) use($app) {
			if(!isset($app['db.migrations.config']) || !is_array($app['db.migrations.config'])) {
				return [];
			}

			// Handle multiple connections configuration (like 'dbs.options')
			if(isset($app['db.migrations.config'][$name]) && is_array($app['db.migrations.config'][$name])) {
				$config = $app['db.migrations.config'][$name];
			}
			// Handle single connection (like 'db.option')
			elseif($name == self::DEFAULT_CONNECTION_NAME) {
				$config = $app['db.migrations.config'];
			}

			return isset($config) ? $config : [];
		});

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

		$app['dispatcher']->addListener(ConsoleEvents::INIT, function(ConsoleEvent $event) {
			$consoleApp = $event->getApplication(); /* @var $console ConsoleApp */
			$consoleApp->add(new DoctrineCommands\StatusCommand());
		});
	}

	public function boot(Application $app) {
		
	}
}