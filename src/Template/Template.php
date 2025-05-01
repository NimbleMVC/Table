<?php

namespace NimblePHP\Table\Template;

use NimblePHP\Framework\Exception\DatabaseException;
use NimblePHP\Framework\Exception\NotFoundException;
use NimblePHP\Framework\Request;
use NimblePHP\Table\Interfaces\ColumnInterface;
use NimblePHP\Table\Table;

class Template
{

    /**
     * Template name
     * @var string
     */
    private string $templateName;

    /**
     * Table instance
     * @var Table
     */
    private Table $tableInstance;

    /**
     * Current url
     * @var ?string
     */
    private ?string $currentUrl;

    /**
     * Create template instance
     * @param Table $tableInstance
     * @param string $templateName
     */
    public function __construct(Table $tableInstance, string $templateName) {
        $this->tableInstance = $tableInstance;
        $this->templateName = $templateName;
        $this->currentUrl = (new Request())->getQuery('url');
    }

    /**
     * Render table
     * @return string
     * @throws NotFoundException
     * @throws DatabaseException
     */
    public function render(): string
    {
        $view = new View();

        return $view->renderViewString(
            $this->templateName,
            [
                'tableId' => $this->tableInstance->getId(),
                'currentUrl' => '/' . $this->currentUrl,
                'tableClasses' => trim($this->tableInstance->getClass()),
                'isAjax' => $this->tableInstance->isAjax(),
                'actions' => $this->tableInstance->getActions(),
                'searchValue' => $this->tableInstance->getSearch(),
                'columns' => $this->tableInstance->getColumns(),
                'filters' => $this->tableInstance->getFilters(),
                'data' => $this->tableInstance->getData(),
                'lang' => Table::$LANGUAGE,
                'tableInstance' => $this->tableInstance,
                'pageCount' => $this->tableInstance->getPageCount(),
                'page' => $this->tableInstance->getPage(),
                'paginationStart' => max(1, $this->tableInstance->getPage() - 3),
                'paginationEnd' => min($this->tableInstance->getPageCount(), $this->tableInstance->getPage() + 3)
            ]
        );
    }

}