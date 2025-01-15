<?php

namespace Nimblephp\table;

use DebugBar\DataCollector\MessagesCollector;
use Krzysztofzylka\File\File;
use Nimblephp\debugbar\Collectors\ModuleCollector;
use Nimblephp\debugbar\Debugbar;
use Nimblephp\framework\Interfaces\ServiceProviderInterface;
use Nimblephp\framework\Kernel;
use Nimblephp\framework\ModuleRegister;
use Nimblephp\twig\Twig;
use Throwable;

class ServiceProvider implements ServiceProviderInterface
{

    public function register(): void
    {
        if (ModuleRegister::isset('nimblephp/migrations') && $_ENV['DATABASE']) {
            $migrations = new \Nimblephp\migrations\Migrations(false, __DIR__ . '/migrations');
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