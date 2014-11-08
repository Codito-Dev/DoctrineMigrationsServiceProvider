<?php

namespace Codito\Silex\DoctrineMigrationsService\Console\Command;

use Codito\Silex\DoctrineMigrationsService\Console\CommandConfigurator;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand as BaseMigrateCommand;

/**
 * Command for executing a migration to a specified version or the latest available version.
 * It's a wrapper for Doctrine Migrations' migrate command.
 *
 * @author Grzegorz Korba <grzegorz.korba@codito.net>
 */
class MigrateCommand extends BaseMigrateCommand {
	use CommandConfigurator;

	protected function configure() {
		parent::configure();

		$this->removeConfigOptions();
		$this->addDbOption();

		$this->setName('doctrine:migrations:migrate');
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		$this->resolveMigrationConfiguration($input, $output);

		parent::execute($input, $output);
	}
}