<?php

namespace Codito\Silex\DoctrineMigrationsService\Console\Command;

use Codito\Silex\DoctrineMigrationsService\Console\CommandConfigurator;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand as BaseGenerateCommand;

/**
 * Command for generating new blank migration classes.
 * It's a wrapper for Doctrine Migrations' diff command.
 *
 * @author Grzegorz Korba <grzegorz.korba@codito.net>
 */
class GenerateCommand extends BaseGenerateCommand {
	use CommandConfigurator;

	protected function configure() {
		parent::configure();

		$this->addDbOption();

		$this->setName('doctrine:migrations:generate');
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		$this->resolveMigrationConfiguration($input, $output);

		parent::execute($input, $output);
	}
}