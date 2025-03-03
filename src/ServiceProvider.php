<?php

namespace NimblePHP\Table;

use DebugBar\DataCollector\MessagesCollector;
use Krzysztofzylka\File\File;
use NimblePHP\debugbar\Collectors\ModuleCollector;
use NimblePHP\debugbar\Debugbar;
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
            $migrations = new \NimblePHP\migrations\Migrations(false, __DIR__ . '/migrations');
            $migrations->runMigrations();
        }

        File::copy(__DIR__ . '/Resources/table.js', Kernel::$projectPath . '/public/assets/table.js');

        if (Kernel::$activeDebugbar) {
            Debugbar::$debugBar->addCollector(new MessagesCollector('Tables'));
        }

        if (ModuleRegister::moduleExistsInVendor('nimblephp/twig')) {
            try {
                Twig::addJsHeader('/assets/table.js');
            } catch (Throwable) {
            }
        }
    }

}