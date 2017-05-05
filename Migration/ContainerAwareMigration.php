<?php

namespace Codito\Silex\DoctrineMigrationsService\Migration;

use Pimple;

/**
 * ContainerAwareMigration defines interface for injecting Pimple container into migrations.
 *
 * @author Grzegorz Korba <grzegorz.korba@codito.net>
 */
interface ContainerAwareMigration
{
    /**
     * Stores container (Silex application instance).
     *
     * @param Pimple $container
     * @return void
     */
    public function setContainer(Pimple $container);
}
