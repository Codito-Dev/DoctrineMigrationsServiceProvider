<?php

namespace Codito\Silex\DoctrineMigrationsService\Console\Command;

use Codito\Silex\DoctrineMigrationsService\Console\CommandConfigurator;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand as BaseExecuteCommand;

/**
 * Command for executing single migrations up or down manually.
 * It's a wrapper for Doctrine Migrations' execute command.
 *
 * @author Grzegorz Korba <grzegorz.korba@codito.net>
 */
class ExecuteCommand extends BaseExecuteCommand {
	use CommandConfigurator;

	protected function configure() {
		parent::configure();

		$this->prepareOptions();

		$this->setName('doctrine:migrations:execute');
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		$this->resolveConfiguration($input, $output);

		parent::execute($input, $output);
	}
}