<?php

namespace Codito\Silex\DoctrineMigrationsService\Console;

use Codito\Silex\DoctrineMigrationsService\Provider\DoctrineMigrationsServiceProvider;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Configuration\Configuration;

trait CommandConfigurator {
	protected function prepareOptions() {
		$definition = $this->getDefinition();

		$definition->setOptions(array_diff_key($definition->getOptions(), array('db-configuration' => '', 'configuration' => '')));

		$this->addOption('db', null, InputOption::VALUE_OPTIONAL, 'Key of a database in application config (Helpful if using multiple connections with "dbs.options")', DoctrineMigrationsServiceProvider::DEFAULT_CONNECTION_NAME);
	}

	protected function resolveConfiguration(InputInterface $input, OutputInterface $output) {
		$silexApp = $this->getApplication()->getSilexApplication();
		$db = $input->getOption('db');

		if(!isset($silexApp['db.migrations'][$db])) {
			throw new \InvalidArgumentException(sprintf('Doctrine Migrations configuration error: unable to configure migrations for "%s" database connection', $db));
		}

		$migrationConfig = $silexApp['db.migrations'][$db];

		if(!isset($migrationConfig['connection']) || !$migrationConfig['connection'] instanceof Connection) {
			throw new \InvalidArgumentException(sprintf('Doctrine Migrations configuration error: Invalid connection for "%s" database', $db));
		}

		// If ValidatorServiceProvider is registered, validate configuration (otherwise we'll pass params to command and it'll handle errors)
		if(isset($silexApp['validator']) && $silexApp['validator'] instanceof Validator) {
			$this->validateConfiguration($migrationConfig);
		}

		// Simple bridge between console and migration manager
		$outputWriter = new OutputWriter(function($message) use($output) {
			return $output->writeln($message);
		});

		$config = new Configuration($migrationConfig['connection'], $outputWriter);
		$config->setName($migrationConfig['name']);
		$config->setMigrationsDirectory($migrationConfig['dir_name']);
		$config->setMigrationsNamespace($migrationConfig['namespace']);
		$config->setMigrationsTableName($migrationConfig['table_name']);
		// IMPORTANT! Migration files are not autoloaded!
		$config->registerMigrationsFromDirectory($migrationConfig['dir_name']);

		$this->setMigrationConfiguration($config);
	}

	protected function validateConfiguration(array $config) {
		//@TODO advanced validation
	}
}