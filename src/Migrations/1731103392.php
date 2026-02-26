<?php

use krzysztofzylka\DatabaseManager\Column;
use krzysztofzylka\DatabaseManager\CreateTable;
use krzysztofzylka\DatabaseManager\Enum\ColumnType;
use krzysztofzylka\DatabaseManager\Table;
use NimblePHP\Migrations\AbstractMigration;

return new class extends AbstractMigration
{

    public function run()
    {
        if ((new Table('module_table_config'))->exists()) {
            return;
        }

        (new CreateTable())
            ->setName('module_table_config')
            ->addIdColumn()
            ->addSimpleVarcharColumn('key', 1024)
            ->addSimpleVarcharColumn('table_id', 1024)
            ->addColumn((new Column())
                ->setName('config')
                ->setType(ColumnType::text)
                ->setNull(true))
            ->addDateModifyColumn()
            ->addDateCreatedColumn()
            ->execute();
    }

};
