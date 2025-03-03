<?php

namespace NimblePHP\Table\Interfaces;

use NimblePHP\Table\Column;

interface ColumnInterface
{

    /**
     * Create column
     * @param string $key
     * @return Column
     */
    public static function create(string $key): ColumnInterface;

    /**
     * Set name
     * @param string $name
     * @return ColumnInterface
     */
    public function setName(string $name): ColumnInterface;

    /**
     * Get name
     * @return string
     */
    public function getName(): string;

    /**
     * Get key
     * @return string
     */
    public function getKey(): string;

    /**
     * Set key
     * @param string $key
     * @return ColumnInterface
     */
    public function setKey(string $key): ColumnInterface;

    /**
     * Set value
     * @param mixed $value
     * @return ColumnInterface
     */
    public function setValue(mixed $value): ColumnInterface;

    /**
     * Get value
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * Set search in field
     * @param mixed $search
     * @return ColumnInterface
     */
    public function setSearch(bool $search): ColumnInterface;

    /**
     * Get search in field
     * @return mixed
     */
    public function getSearch(): mixed;

    /**
     * Set style
     * @param array $styles
     * @return ColumnInterface
     */
    public function setStyle(array $styles): ColumnInterface;

    /**
     * Get style
     * @return array
     */
    public function getStyle(): array;

    /**
     * Get style as string
     * @return string
     */
    public function getStyleAsString(): string;


}