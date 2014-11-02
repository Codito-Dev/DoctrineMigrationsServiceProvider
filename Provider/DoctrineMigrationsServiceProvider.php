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

		$dbs = isset($app['dbs']) ? $app['dbs'] : ['default' => $app['db']];

		foreach($dbs as $name => $connection) {
			$app['db.migrations.' . $name] = $app->share(function ($app) use($name, $connection) {
				$dbMigrationConfig = isset($app['db.migrations.config.' . $name]) ? $app['db.migrations.config.' . $name] : [];

				return $app['db.migrations.config._proto']($connection, $dbMigrationConfig);
			});
		}

		$app['dispatcher']->addListener(ConsoleEvents::INIT, function(ConsoleEvent $event) {
			$consoleApp = $event->getApplication(); /* @var $console ConsoleApp */
			$consoleApp->add(new DoctrineCommands\StatusCommand());
		});
	}

	public function boot(Application $app) {
		
	}
}