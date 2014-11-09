Doctrine Migrations Service Provider
====================================

Provides [Doctrine Migrations](https://github.com/doctrine/migrations) commands in [Silex](http://silex.sensiolabs.org/) applications by extending console with additional commands. Those commands are wrappers for base Doctrine Migrations commands and for proper working require console application to be instance of console provided by [KnpLabs Console Service Provider](https://github.com/KnpLabs/ConsoleServiceProvider), because commands need access to some services and/or config options to properly resolve migrations configuration.

Requirements
------------

 * PHP >= 5.4 because of traits and short array syntax.

Installation
------------

Add entries to `composer.json`:

```json
"require": {
	"codito/doctrine-migrations-service-provider": "~0.3",
	"doctrine/migrations": "@dev"
}
```

Since `doctrine/migrations` does not have stable release yet, I didn't want to require it internally in `codito/doctrine-migrations-service-provider` because it could force you to change your `minimum-stability` config in order to install it. Adding both in your root project means you can have `minimum-stability: stable` and define `@dev` only for `doctrine/migrations`.

Configuration
-------------

In order to use Doctrine Migrations commands in your console, you have to configure few things:

 * `DoctrineServiceProvider` (one of Silex's default providers)
 * `ConsoleServiceProvider` from [here](https://github.com/KnpLabs/ConsoleServiceProvider)
 * `DoctrineOrmServiceProvider` from [here](https://github.com/dflydev/dflydev-doctrine-orm-service-provider), optionally (required only for `migrations:diff` command)
 * `DoctrineMigrationsServiceProvider` itself

`DoctrineMigrationsServiceProvider` supports configuration both for single and multiple connections/entity managers.

Example config
--------------

Register `DoctrineServiceProvider` (can be also configured with `db.options`, then it will be `default` connection)

```php
$app->register(new DoctrineServiceProvider(), array(
	'dbs.options' => array(
		'some_connection' => array(
			'driver'   => 'pdo_mysql',
			'dbname'   => 'silex',
			'host'     => 'localhost',
			'user'     => 'root',
			'password' => null,
			'port'     => null,
		)
	)
));
```

Register `ConsoleServiceProvider` (keep in mind that `console.project_directory` should point to root of your project)

```php
$app->register(new Knp\Provider\ConsoleServiceProvider(), array(
    'console.name'              => 'Silex App',
    'console.version'           => '1.0.0',
    'console.project_directory' => __DIR__ . '/..' // Depends on your project structure!
));
```

Register `DoctrineMigrationsServiceProvider`:

```php
$app->register(new Codito\Silex\DoctrineMigrationsService\Provider\DoctrineMigrationsServiceProvider(), array(
	'db.migrations.options' => array(
		'some_connection' => array(
			'dir_name' => realpath(__DIR__ . '/Application/Migrations'),
			'namespace' => 'Application\\Migrations',
			'table_name' => 'migration_versions',
			'name' => 'Application Migrations',
		)
	)
));
```

Configuration of `DoctrineMigrationsServiceProvider` is always under `db.migrations.options`, regardless to single or multiple configs. Those configs are related to `db.options`/`dbs.options` and names must match in order to work correctly.

Optionally, if you need `migrations:diff` command, you may want to register 

```php
$app->register(new Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider(), array(
	"orm.proxies_dir" => __DIR__ . '/../var/orm',
	"orm.ems.options" => array(
		'some_entity_manager' => array(
			'connection' => 'some_connection', // Important if you have custom connection name
			"mappings" => array(
				// Using actual filesystem paths
				array(
					"type" => "annotation",
					"namespace" => "Application\Entity",
					"path" => __DIR__ . "/Application/Entity",
					'use_simple_annotation_reader' => false // Support for "use Doctrine\ORM\Mapping AS ORM" -> "@ORM\Entity"
				),
			),
		)
	),
));
```

If you want to use complex annotations, like `@ORM\Entity`, you have to set `use_simple_annotation_reader` to `false` like above. However it requires to configure `AnnotationRegistry` on your own, like:

```php
$loader = require_once __DIR__.'/../vendor/autoload.php';
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
```

It should be done in `console` executable file.

Usage
-----

Just run `php bin/console` (or wherever you have `console`) and commands list will appear. Each command has own help, to view it just type `php bin/console some:command:from:your:commands:list --help`.

If you did everything properly, complete migration commands would look like:

```
doctrine
  doctrine:migrations:diff       Generate a migration by comparing your current database to your mapping information.
  doctrine:migrations:execute    Execute a single migration version up or down manually.
  doctrine:migrations:generate   Generate a blank migration class.
  doctrine:migrations:latest     Outputs the latest version number
  doctrine:migrations:migrate    Execute a migration to a specified version or the latest available version.
  doctrine:migrations:status     View the status of a set of migrations.
  doctrine:migrations:version    Manually add and delete migration versions from the version table.
```

Please notice
-------------

Depending on your config you have to (or don't) pass `--db`/`--em` param to command. `--db` is used for most of commands, `--em` is only for `doctrine:migrations:diff` and it will automatically set `--db` option based on `connection` attribute in entity manager's config.