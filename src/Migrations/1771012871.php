<?php

use krzysztofzylka\DatabaseManager\Column;
use krzysztofzylka\DatabaseManager\CreateIndex;
use krzysztofzylka\DatabaseManager\Enum\ColumnType;
use NimblePHP\Migrations\AbstractMigration;

return new class extends AbstractMigration
{

    public function run(): void
    {
        (new CreateIndex('module_table_config'))
            ->setName('module_table_key_tableid')
            ->addColumn('key')
            ->addColumn('table_id')
            ->execute();
    }

};
