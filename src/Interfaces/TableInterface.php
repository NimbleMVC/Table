<?php

namespace NimblePHP\Table\Interfaces;

use krzysztofzylka\DatabaseManager\Exception\DatabaseManagerException;
use NimblePHP\Framework\Exception\DatabaseException;
use NimblePHP\Framework\Exception\NimbleException;
use NimblePHP\Framework\Interfaces\ModelInterface;
use NimblePHP\Table\Table;

interface TableInterface
{

    /**
     * Add data
     * @param array $data
     * @return void
     */
    public function setData(array $data): void;

    /**
     * Get data
     * @return array
     */
    public function getData(): array;

    /**
     * Render table
     * @return string
     * @throws DatabaseException
     * @throws DatabaseManagerException
     * @throws NimbleException
     */
    public function render(): string;

    /**
     * Add column
     * @param ColumnInterface $column
     * @return void
     */
    public function addColumn(ColumnInterface $column): void;

    /**
     * Prepare column value
     * @param ColumnInterface $column
     * @param array $data
     * @return mixed
     */
    public function prepareColumnValue(ColumnInterface $column, array $data): string;

    /**
     * Get table id
     * @return ?string
     */
    public function getId(): ?string;

    /**
     * Set table id
     * @param ?string $id
     * @return void
     */
    public function setId(?string $id): void;

    /**
     * Get table model
     * @return ?ModelInterface
     */
    public function getModel(): ?ModelInterface;

    /**
     * Set table model
     * @param ?ModelInterface $model
     * @return void
     */
    public function setModel(?ModelInterface $model): void;

    /**
     * Get conditions
     * @return array
     */
    public function getConditions(): array;

    /**
     * Set conditions
     * @param array $conditions
     * @return void
     */
    public function setConditions(array $conditions): void;

    /**
     * Get limit
     * @return int
     */
    public function getLimit(): int;

    /**
     * Set limit
     * @param int $limit
     * @return void
     */
    public function setLimit(int $limit): void;

    /**
     * Get actual page
     * @return int
     */
    public function getPage(): int;

    /**
     * Set actual page
     * @param int $page
     * @return void
     */
    public function setPage(int $page): void;

    /**
     * Get page count
     * @return int
     */
    public function getPageCount(): int;

    /**
     * Set page count
     * @param int $pageCount
     * @return void
     */
    public function setPageCount(int $pageCount): void;

    /**
     * Set config
     * @return void
     */
    public function readConfig(): void;

    /**
     * Save config
     * @return void
     */
    public function saveConfig(): void;

    /**
     * Query config
     * @return void
     */
    public function queryConfig(): void;

    /**
     * Set additional table class
     * @param string $class
     * @return void
     */
    public function setClass(string $class = '');

    /**
     * Get additional table class
     * @return string
     */
    public function getClass(): string;

    /**
     * Get ajax key
     * @return null|int|string
     */
    public function getAjaxKey(): null|int|string;

    /**
     * Set ajax key
     * @param int|string|null $ajaxKey
     */
    public static function setAjaxKey(null|int|string $ajaxKey): void;

    /**
     * Set ajax mode
     * @param bool $ajax
     * @return Table
     */
    public function setAjax(bool $ajax = true): self;

    /**
     * Is ajax
     * @return bool
     */
    public function isAjax(): bool;

    /**
     * Get columns
     * @return array
     */
    public function getColumns(): array;

    /**
     * Get actions
     * @return array
     */
    public function getActions(): array;

    /**
     * Get ajax config
     * @return void
     * @throws DatabaseManagerException
     */
    public function getAjaxConfig(): void;

    /**
     * Save ajax config
     * @return void
     * @throws DatabaseManagerException
     */
    public function saveAjaxConfig(): void;

}