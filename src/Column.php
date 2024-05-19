<?php

namespace Nimblephp\table;

use Nimblephp\table\Interfaces\ColumnInterface;


class Column implements ColumnInterface
{

    /**
     * Column name
     * @var string
     */
    protected string $name;

    /**
     * Column key
     * @var string
     */
    protected string $key;

    /**
     * Custom value
     * @var mixed
     */
    protected mixed $value = null;

    /**
     * Create column
     * @param string $key
     * @return ColumnInterface
     */
    public static function create(string $key): ColumnInterface
    {
        $column = new Column();
        $column->setKey($key);

        return $column;
    }

    /**
     * Set name
     * @param string $name
     * @return ColumnInterface
     */
    public function setName(string $name): ColumnInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get key
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Set key
     * @param string $key
     * @return ColumnInterface
     */
    public function setKey(string $key): ColumnInterface
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set value
     * @param mixed $value
     * @return ColumnInterface
     */
    public function setValue(mixed $value): ColumnInterface
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

}