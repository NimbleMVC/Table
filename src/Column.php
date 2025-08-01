<?php

namespace NimblePHP\Table;

use NimblePHP\Table\Interfaces\ColumnInterface;


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
     * Search in column
     * @var bool
     */
    protected bool $search = true;

    /**
     * Custom value
     * @var mixed
     */
    protected mixed $value = null;

    /**
     * Styles
     * @var array
     */
    protected array $style = [];

    /**
     * Sortable
     * @var bool
     */
    protected bool $sortable = true;

    /**
     * Create column
     * @param string $key
     * @param string|null $name
     * @return ColumnInterface
     */
    public static function create(string $key, ?string $name = null): ColumnInterface
    {
        $column = new Column();
        $column->setKey($key);

        if (!is_null($name)) {
            $column->setName($name);
        }

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

    /**
     * Set search in field
     * @param mixed $search
     * @return ColumnInterface
     */
    public function setSearch(bool $search): ColumnInterface
    {
        $this->search = $search;

        return $this;
    }

    /**
     * Get search in field
     * @return mixed
     */
    public function getSearch(): mixed
    {
        return $this->search;
    }

    /**
     * Set styles
     * @param array $styles
     * @return ColumnInterface
     */
    public function setStyle(array $styles = []): ColumnInterface
    {
        $this->style = $styles;

        return $this;
    }

    /**
     * Set style
     * @return array
     */
    public function getStyle(): array
    {
        return $this->style;
    }

    /**
     * Set sortable
     * @param bool $sortable
     * @return ColumnInterface
     */
    public function setSortable(bool $sortable): ColumnInterface
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Is sortable
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * Get style as string
     * @return string
     */
    public function getStyleAsString(): string
    {
        $data = implode('; ', array_map(
            function ($key, $value) {
                return "$key: $value";
            },
            array_keys($this->getStyle()),
            $this->getStyle()
        ));

        if (empty(trim($data))) {
            return '';
        }

        return $data . ';';
    }

}