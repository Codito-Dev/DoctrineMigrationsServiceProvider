<?php

namespace Codito\Silex\DoctrineMigrationsService\Console;

use Codito\Silex\DoctrineMigrationsService\Provider\DoctrineMigrationsServiceProvider;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\DBAL\Migrations\Configuration\Configuration;

trait CommandConfigurator {
	protected function prepareOptions() {
		$definition = $this->getDefinition();

		$definition->setOptions(array_diff_key($definition->getOptions(), array('db-configuration' => '', 'configuration' => '')));

		$this->addOption('db', null, InputOption::VALUE_OPTIONAL, 'Key of a database in application config (Helpful if using multiple connections with "dbs.options")', DoctrineMigrationsServiceProvider::DEFAULT_CONNECTION_NAME);
	}

	protected function resolveConfiguration(InputInterface $input) {
		$silexApp = $this->getApplication()->getSilexApplication();
		$db = $input->getOption('db');

		if(!isset($silexApp['db.migrations.' . $db])) {
			throw new \InvalidArgumentException(sprintf('Doctrine Migrations configuration error: unable to configure migrations for "%s" database connection', $db));
		}

		$this->setMigrationConfiguration();
	}
}