<?php

namespace Nimblephp\table\Interfaces;

use Nimblephp\framework\Interfaces\ModelInterface;

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

}