<?php

namespace Codito\Silex\DoctrineMigrationsService\Console;

use Codito\Silex\DoctrineMigrationsService\Provider\DoctrineMigrationsServiceProvider;
use Codito\Silex\DoctrineMigrationsService\Migration\ContainerAwareMigration;
use Pimple\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Version;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;

/**
 * CommandConfigurator defines common methods for configuring commands
 * 
 * @author Grzegorz Korba <grzegorz.korba@codito.net>
 */
trait CommandConfigurator
{

    /**
     * Adds "db" option to command
     */
    protected function addDbOption($isChild = false)
    {
        $this->addOption(
            'db',
            null,
            InputOption::VALUE_OPTIONAL,
            $isChild ?
                'This option will be automatically set based on entity manager' :
                'Key of a database in application config (Helpful if using multiple connections with "dbs.options")',
            DoctrineMigrationsServiceProvider::DEFAULT_CONNECTION_NAME
        );
    }

    /**
     * Adds "em" option to command
     */
    protected function addEmOption()
    {
        $this->addOption(
            'em',
            null,
            InputOption::VALUE_OPTIONAL,
            'Name of a entity manager in application config (Helpful if using multiple connections with "orm.ems.options")',
            DoctrineMigrationsServiceProvider::DEFAULT_ENTITY_MANAGER_NAME
        );
    }

    /**
     * Configures connection for command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \InvalidArgumentException If migrations configuration for specified (or default) connection is invalid
     */
    protected function resolveMigrationConfiguration(
        InputInterface $input,
        OutputInterface $output
    ) {
        /** @var Container $silexApp */
        $silexApp = $this->getApplication()->getSilexApplication();
        $db       = $input->getOption('db');

        if (!isset($silexApp['db.migrations'][$db])) {
            throw new \InvalidArgumentException(sprintf(
                'Doctrine Migrations configuration error: unable to configure migrations for "%s" database connection',
                $db
            ));
        }

        $migrationConfig = $silexApp['db.migrations'][$db];

        if (!isset($migrationConfig['connection']) || !$migrationConfig['connection'] instanceof Connection) {
            throw new \InvalidArgumentException(sprintf(
                'Doctrine Migrations configuration error: Invalid connection for "%s" database',
                $db
            ));
        }

        // If ValidatorServiceProvider is registered, validate configuration (otherwise we'll pass params to command and it'll handle errors)
        if (isset($silexApp['validator']) && $silexApp['validator'] instanceof Validator) {
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

        // Support for migrations where Pimple container (Silex app) is required
        $this->injectContainerToMigrations($silexApp, $config->getMigrations());
    }

    protected function validateConfiguration(array $config)
    {
        //@TODO advanced validation
    }

    /**
     * Resolves entity manager's configuration and injects "em" helper to console application
     * @param InputInterface $input
     * @throws \InvalidArgumentException
     */
    protected function resolveEntityManagerConfiguration(InputInterface $input)
    {
        $silexApp = $this->getApplication()->getSilexApplication();
        $em       = $input->getOption('em');

        if (!class_exists('\\Doctrine\\ORM\\Tools\\Console\\Helper\\EntityManagerHelper')) {
            throw new \InvalidArgumentException('Doctrine EntityManagerHelper class was not found');
        }

        if (!isset($silexApp['orm.ems']) || !isset($silexApp['orm.ems'][$em])) {
            throw new \InvalidArgumentException(sprintf(
                'Doctrine Migrations configuration error: "%s" entity manager is not defined properly',
                $em
            ));
        }

        // At this point entity manager has configured connection (or uses default one)
        $connection = $silexApp['orm.ems.options'][$em]['connection'];

        if (!isset($silexApp['dbs']) || !isset($silexApp['dbs'][$connection])) {
            throw new \RuntimeException(sprintf(
                'Doctrine Migrations configuration error: "%s" entity manager uses "%s" connection, but it is not defined',
                $em,
                $connection
            ));
        }

        // Register entity manager helper with "em" alias for migrations:diff command
        $this->getHelperSet()->set(new EntityManagerHelper($silexApp['orm.ems'][$em]), 'em');
        $input->setOption('db', $connection);
    }

    /**
     * @param Container $container
     * @param Version[] $versions
     *
     * Injects the container to migrations aware of it
     */
    private function injectContainerToMigrations(Container $container, array $versions)
    {
        foreach ($versions as $version) {
            $migration = $version->getMigration();

            if ($migration instanceof ContainerAwareMigration) {
                $migration->setContainer($container);
            }
        }
    }
}
