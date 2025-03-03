<?php

use krzysztofzylka\DatabaseManager\Column;
use krzysztofzylka\DatabaseManager\Enum\ColumnType;

return new class extends \NimblePHP\migrations\AbstractMigration
{

    public function run()
    {
        (new \krzysztofzylka\DatabaseManager\CreateTable())
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
