<?php

namespace Codito\Silex\DoctrineMigrationsService\Console\Command;

use Codito\Silex\DoctrineMigrationsService\Console\CommandConfigurator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand as BaseDiffCommand;

/**
 * Command for generate migration classes by comparing your current database schema
 * to your mapping information.
 * It's a wrapper for Doctrine Migrations' diff command.
 *
 * @author Grzegorz Korba <grzegorz.korba@codito.net>
 */
class DiffCommand extends BaseDiffCommand
{
    use CommandConfigurator;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->addDbOption(true);
        $this->addEmOption();

        $this->setName('doctrine:migrations:diff');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->resolveEntityManagerConfiguration($input);
        $this->resolveMigrationConfiguration($input, $output);

        parent::execute($input, $output);
    }
}