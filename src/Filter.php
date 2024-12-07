<?php

namespace Nimblephp\table;

use krzysztofzylka\DatabaseManager\Condition;
use Krzysztofzylka\HtmlGenerator\HtmlGenerator;

class Filter
{

    protected array $condition = [];

    protected array $baseCondition = [];

    protected ?string $title = null;

    protected string $type = 'select';

    protected string $key = '';

    protected mixed $content = null;

    protected string $value;

    public static function create(string $key, ?string $type = null): self
    {
        $filter = new self();
        $filter->setType($type ?? 'select');
        $filter->setKey($key);

        return $filter;
    }

    public function render(Table $table): string
    {
        $content = '';

        switch ($this->getType()) {
            case 'select':
                $selectContent = '';

                if (is_array($this->getContent())) {
                    foreach ($this->getContent() as $key => $value) {
                        $option = HtmlGenerator::createTag('option')->setContent($value)->addAttribute('value', $key);

                        if ($this->getValue() === $key) {
                            $option->addAttribute('selected', 'selected');
                        }

                        $selectContent .= $option;
                    }
                }

                $content .= HtmlGenerator::createTag('select')
                    ->setClass('form-select form-select-sm ajax-form')
                    ->addAttribute('type', 'filter')
                    ->setName('filter-' . $this->getKey())
                    ->setContent($selectContent);

                break;
            case 'date':
                $content .= HtmlGenerator::createTag('input')
                    ->setClass('form-control form-control-sm ajax-form')
                    ->addAttribute('type', 'date')
                    ->setName('filter-' . $this->getKey())
                    ->addAttribute('value', $this->getValue() ?? '');

                break;
        }

        return '<div style="position: relative; float: left;" class="me-2">
            <label for="floatingSelect" style="font-size: 0.8em; position: absolute; top: -12px; left: 5px; padding: 1px;" class="bg-body user-select-none">' . $this->getTitle() . '</label>
            ' . $content . '
        </div>';
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setCondition(array $condition): self
    {
        $this->baseCondition = $condition;
        $this->condition = $condition;

        return $this;
    }

    public function getCondition(): array
    {
        if (!isset($this->value)) {
            return [];
        }

        return $this->condition;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param mixed $content
     */
    public function setContent(mixed $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        $condition = $this->baseCondition;

        foreach ($condition as $conditionKey => $conditionValue) {
            if ($conditionValue === '%VALUE%') {
                if ($value === '%ALL%') {
                    unset($condition[$conditionKey]);
                    continue;
                }

                $condition[$conditionKey] = $this->getValue();
            } elseif ($conditionValue instanceof Condition) {
                /** @var Condition $conditionValue */
                if ($conditionValue->getValue() === '%VALUE%') {
                    if (empty(trim($value))) {
                        unset($condition[$conditionKey]);
                        continue;
                    }

                    $condition[$conditionKey] = new Condition($conditionValue->getColumn(true), $conditionValue->getOperator(), $value);
                }
            }
        }

        $this->condition = $condition;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value ?? null;
    }

}