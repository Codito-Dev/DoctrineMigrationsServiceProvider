<?php

namespace Codito\Silex\DoctrineMigrationsService\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Knp\Console\Application as ConsoleApp;
use Knp\Console\ConsoleEvents;
use Knp\Console\ConsoleEvent;
use Codito\Silex\DoctrineMigrationsService\Console\Command as DoctrineCommands;

class DoctrineMigrationsServiceProvider implements ServiceProviderInterface {
	public function register(Application $app) {
		$app['dispatcher']->addListener(ConsoleEvents::INIT, function(ConsoleEvent $event) {
			$consoleApp = $event->getApplication(); /* @var $console ConsoleApp */
			$consoleApp->add(new DoctrineCommands\StatusCommand());
		});
	}

	public function boot(Application $app) {
		
	}
}