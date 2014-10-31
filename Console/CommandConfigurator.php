<?php

namespace Codito\Silex\DoctrineMigrationsService\Console;

use Symfony\Component\Console\Input\InputOption;

trait CommandConfigurator {
	protected function prepareOptions() {
		$definition = $this->getDefinition();

		$definition->setOptions(array_diff_key($definition->getOptions(), array('db-configuration' => '', 'configuration' => '')));

		$this->addOption('db', null, InputOption::VALUE_REQUIRED, 'Key of a database in application config (Helpful if using multiple connections with "dbs.options")');
	}

	protected function resolveConfiguration() {
	}
}