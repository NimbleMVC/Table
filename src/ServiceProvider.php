<?php

namespace NimblePHP\Table;

use Krzysztofzylka\File\File;
use NimblePHP\Framework\Interfaces\ServiceProviderInterface;
use NimblePHP\Framework\Kernel;
use NimblePHP\Framework\ModuleRegister;
use NimblePHP\Twig\Twig;
use Throwable;

class ServiceProvider implements ServiceProviderInterface
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

}