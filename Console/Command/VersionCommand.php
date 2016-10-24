<?php

namespace Codito\Silex\DoctrineMigrationsService\Console\Command;

use Codito\Silex\DoctrineMigrationsService\Console\CommandConfigurator;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand as BaseVersionCommand;

/**
 * Command for manually adding and deleting migration versions from the version table.
 * It's a wrapper for Doctrine Migrations' version command.
 *
 * @author Grzegorz Korba <grzegorz.korba@codito.net>
 */
class VersionCommand extends BaseVersionCommand {
	use CommandConfigurator;

	protected function configure() {
		parent::configure();

		$this->addDbOption();

		$this->setName('doctrine:migrations:version');
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		$this->resolveMigrationConfiguration($input, $output);

		parent::execute($input, $output);
	}
}