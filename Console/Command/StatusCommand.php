<?php

namespace Codito\Silex\DoctrineMigrationsService\Console\Command;

use Codito\Silex\DoctrineMigrationsService\Console\CommandConfigurator;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DoctrineCommandHelper;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand as BaseStatusCommand;

/**
 * Command to view the status of a set of migrations.
 *
 * @author Grzegorz Korba <grzegorz.korba@codito.net>
 */
class StatusCommand extends BaseStatusCommand {
	use CommandConfigurator;

	protected function configure() {
		parent::configure();

		$this->prepareOptions();

		$this->setName('doctrine:migrations:status');
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		$this->resolveConfiguration();

		parent::execute($input, $output);
	}
}