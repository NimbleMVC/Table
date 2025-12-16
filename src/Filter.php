<?php

namespace NimblePHP\Table;

use krzysztofzylka\DatabaseManager\Condition;
use Krzysztofzylka\HtmlGenerator\HtmlGenerator;
use NimblePHP\Framework\Log;
use NimblePHP\Table\Interfaces\FilterInterface;

class Filter implements FilterInterface
{

    /**
     * Conditions
     * @var array
     */
    protected array $condition = [];

    /**
     * Base condition
     * @var array
     */
    protected array $baseCondition = [];

    /**
     * Title
     * @var string|null
     */
    protected ?string $title = null;

    /**
     * Type
     * @var string
     */
    protected string $type = 'select';

    /**
     * Key
     * @var string
     */
    protected string $key = '';

    /**
     * Content
     * @var mixed|null
     */
    protected mixed $content = null;

    /**
     * Value
     * @var string
     */
    protected string $value;

    /**
     * Create filter
     * @param string $key
     * @param string|null $type
     * @return self
     */
    public static function create(string $key, ?string $type = null): self
    {
        $filter = new self();
        $filter->setType($type ?? 'select');
        $filter->setKey($key);

        return $filter;
    }

    /**
     * Render filter
     * @param Table $table
     * @return string
     */
    public function render(Table $table): string
    {
        $content = '';
        $title = true;

        switch ($this->getType()) {
            case 'select':
                $selectContent = '';

                if (is_array($this->getContent())) {
                    foreach ($this->getContent() as $key => $value) {
                        $option = HtmlGenerator::createTag('option')->setContent($value)->addAttribute('value', $key);

                        if ((string)$this->getValue() === (string)$key) {
                            $option->addAttribute('selected', 'selected');
                        }

                        $selectContent .= $option;
                    }
                }

                $content .= HtmlGenerator::createTag('select')
                    ->setClass('form-select form-select-sm ajax-form mb-2')
                    ->addAttribute('type', 'filter')
                    ->setName('filter-' . $this->getKey())
                    ->setContent($selectContent);

                break;
            case 'date':
                $content .= HtmlGenerator::createTag('input')
                    ->setClass('form-control form-control-sm ajax-form mb-2')
                    ->addAttribute('type', 'date')
                    ->setName('filter-' . $this->getKey())
                    ->addAttribute('value', $this->getValue() ?? '');

                break;
            case 'checkbox':
                $title = false;

                $checkbox = HtmlGenerator::createTag('input')
                    ->setClass('form-check-input ajax-checkbox mb-2')
                    ->setName('filter-' . $this->getKey())
                    ->addAttribute('type', 'checkbox')
                    ->setId('FilterCheckbox_' . $this->getKey());

                if ((bool)$this->getValue()) {
                    $checkbox->addAttribute('checked', 'checked');
                }

                $content .= HtmlGenerator::createTag('div')
                    ->setClass('form-check')
                    ->addAttribute('style', 'border: 1px solid rgb(222, 226, 230); border-radius: 5px; padding: 2px; padding-left: 32px; padding-right: 7px; position: relative; ' . (Table::$layout === 'modern' ? 'top: -7px;' : ''))
                    ->setContent(
                        $checkbox
                        . HtmlGenerator::createTag('label')
                            ->setClass('form-check-label')
                            ->addAttribute('for', 'FilterCheckbox_' . $this->getKey())
                            ->setContent($this->getTitle())
                    );
                break;
        }

        return '<div style="position: relative; float: left;" class="me-2">
            ' . ($title ? '<label for="floatingSelect" style="font-size: 0.8em; position: absolute; top: -12px; left: 5px; padding: 1px;" class="bg-body user-select-none">' . $this->getTitle() . '</label>' : '') . '
            ' . $content . '
        </div>';
    }

    /**
     * Set title
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set condition
     * @param array $condition
     * @return $this
     */
    public function setCondition(array $condition): self
    {
        $this->baseCondition = $condition;
        $this->condition = $condition;

        return $this;
    }

    /**
     * Get condition
     * @return array
     */
    public function getCondition(): array
    {
        if (!isset($this->value)) {
            return [];
        }

        if ($this->getType() === 'checkbox' && $this->value === '0') {
            return [];
        }

        return $this->condition;
    }

    /**
     * Set type
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $key
     * @return Filter
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
     * @return Filter
     */
    public function setContent(mixed $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     * @return mixed
     */
    public function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * Set value
     * @param mixed $value
     * @return $this
     */
    public function setValue(mixed $value): self
    {
        $this->value = $value;
        $condition = $this->baseCondition;

        foreach ($condition as $conditionKey => $conditionValue) {
            if ($value === '%ALL%') {
                unset($condition[$conditionKey]);
                continue;
            } elseif (!$conditionValue instanceof Condition && str_contains($conditionValue, '%VALUE%')) {
                $condition[$conditionKey] = str_replace('%VALUE%', $this->getValue(), $conditionValue);
            } elseif ($conditionValue instanceof Condition) {
                if (!is_array($conditionValue->getValue())) {
                    if (str_contains($conditionValue->getValue(), '%VALUE%')) {
                        $condition[$conditionKey] = new Condition(
                            $conditionValue->getColumn(true),
                            $conditionValue->getOperator(),
                            str_replace('%VALUE%', $this->getValue(), $conditionValue->getValue())
                        );
                    }
                }
            }
        }

        $this->condition = $condition;

        return $this;
    }

    /**
     * Get value
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value ?? null;
    }

}