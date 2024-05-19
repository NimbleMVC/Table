<?php

namespace Nimblephp\table\Interfaces;

use Nimblephp\table\Column;

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

}