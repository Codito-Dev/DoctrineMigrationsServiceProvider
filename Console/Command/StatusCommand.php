<?php

namespace Codito\Silex\DoctrineMigrationsService\Console\Command;

use Codito\Silex\DoctrineMigrationsService\Console\CommandConfigurator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand as BaseStatusCommand;

/**
 * Command to view the status of a set of migrations.
 * It's a wrapper for Doctrine Migrations' status command.
 *
 * @author Grzegorz Korba <grzegorz.korba@codito.net>
 */
class StatusCommand extends BaseStatusCommand
{
    use CommandConfigurator;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->addDbOption();

        $this->setName('doctrine:migrations:status');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->resolveMigrationConfiguration($input, $output);

        parent::execute($input, $output);
    }
}
