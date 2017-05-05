<?php

namespace Codito\Silex\DoctrineMigrationsService\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Pimple;

/**
 * AbstractContainerAwareMigration is a base migration class
 * for migrations which require Pimple container (Silex app).
 *
 * @author Grzegorz Korba <grzegorz.korba@codito.net>
 */
abstract class AbstractContainerAwareMigration extends AbstractMigration implements ContainerAwareMigration
{
    /**
     * @var Pimple
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(Pimple $container)
    {
        $this->container = $container;
    }
}
