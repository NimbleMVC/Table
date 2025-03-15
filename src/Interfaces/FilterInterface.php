<?php

namespace NimblePHP\Table\Interfaces;

use NimblePHP\Table\Table;

/**
 * Filter interface
 */
interface FilterInterface
{
    /**
     * Create filter
     * @param string $key
     * @param string|null $type
     * @return self
     */
    public static function create(string $key, ?string $type = null): self;

    /**
     * Render filter
     * @param Table $table
     * @return string
     */
    public function render(Table $table): string;

    /**
     * Set title
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title): self;

    /**
     * Get title
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * Set condition
     * @param array $condition
     * @return $this
     */
    public function setCondition(array $condition): self;

    /**
     * Get condition
     * @return array
     */
    public function getCondition(): array;

    /**
     * Set type
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self;

    /**
     * Get type
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Set key
     * @param string $key
     * @return FilterInterface
     */
    public function setKey(string $key): self;

    /**
     * Get key
     * @return string
     */
    public function getKey(): string;

    /**
     * Set content
     * @param mixed $content
     * @return FilterInterface
     */
    public function setContent(mixed $content): self;

    /**
     * Get content
     * @return mixed
     */
    public function getContent(): mixed;

    /**
     * Set value
     * @param string $value
     * @return FilterInterface
     */
    public function setValue(string $value): self;

    /**
     * Get value
     * @return string|null
     */
    public function getValue(): ?string;

}