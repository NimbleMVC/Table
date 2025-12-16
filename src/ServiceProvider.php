<?php

namespace NimblePHP\Table;

use Krzysztofzylka\File\File;
use NimblePHP\Framework\Interfaces\ServiceProviderInterface;
use NimblePHP\Framework\Interfaces\ServiceProviderUpdateInterface;
use NimblePHP\Framework\Kernel;
use NimblePHP\Framework\ModuleRegister;
use krzysztofzylka\DatabaseManager\Exception\ConnectException;
use krzysztofzylka\DatabaseManager\Exception\DatabaseManagerException;
use NimblePHP\Framework\Exception\DatabaseException;
use NimblePHP\Framework\Exception\NimbleException;
use NimblePHP\Migrations\Exceptions\MigrationException;
use NimblePHP\Migrations\Migrations;
use NimblePHP\Twig\Twig;
use Throwable;

class ServiceProvider implements ServiceProviderInterface, ServiceProviderUpdateInterface
{

    public function register(): void
    {
        if (ModuleRegister::isset('nimblephp/migrations') && $_ENV['DATABASE']) {
            $migrations = new \NimblePHP\Migrations\Migrations(false, __DIR__ . '/migrations');
            $migrations->runMigrations();
        }

        File::copy(__DIR__ . '/Resources/table.js', Kernel::$projectPath . '/public/assets/table.js');

        if (ModuleRegister::moduleExistsInVendor('nimblephp/twig')) {
            try {
                Twig::addJsHeader('/assets/table.js');
            } catch (Throwable) {
            }
        }
    }

    /**
     * Execute on application update - runs pending migrations
     * @return void
     * @throws DatabaseException
     * @throws NimbleException
     * @throws MigrationException
     * @throws Throwable
     * @throws ConnectException
     * @throws DatabaseManagerException
     */
    public function onUpdate(): void
    {
        $migration = new Migrations(Kernel::$projectPath, __DIR__ . '/Migrations', 'module_table');
        $migration->runMigrations();
    }

}