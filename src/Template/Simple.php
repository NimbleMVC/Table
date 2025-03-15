<?php

namespace NimblePHP\Table\Template;

use Krzysztofzylka\HtmlGenerator\HtmlGenerator;
use NimblePHP\Table\Column;
use NimblePHP\Table\Filter;
use NimblePHP\Table\Table;

class Simple
{

    public Table $table;

    public static array $CLASSES = [
        'main-div' => 'm-2',
        'header' => 'row position-relative',
        'table' => 'table table-striped m-0',
        'table-div' => 'table-responsive-md overflow-auto',
        'table-thead' => '',
        'table-thead-tr' => '',
        'table-tbody' => '',
        'table-header' => 'd-flex flex-wrap justify-content-between mb-2',
        'action-div' => 'pt-1 col-12 col-md-auto mb-2 mb-md-0',
        'action-button' => 'btn btn-primary w-100',
        'action-search-div' => 'col-12 col-md-auto d-flex justify-content-end',
        'action-search-input' => 'form-control me-2 form-control-sm w-100',
        'table-footer' => 'p-1 mt-2',
        'table-footer-pagination-ul' => 'justify-content-end m-0 pagination-sm',
        'table-footer-pagination-a' => 'page-link',
        'table-footer-pagination-li' => 'page-item'
    ];

    /**
     * Render table
     * @param Table $table
     * @return string
     * @throws \NimblePHP\Framework\Exception\DatabaseException
     */
    public function render(Table $table): string
    {
        $this->table = $table;

        return HtmlGenerator::createTag('div')
            ->setContent(
                HtmlGenerator::createTag('div')
                    ->setClass(trim('table-header ' . self::$CLASSES['table-header']))
                    ->setContent($this->renderHeader())
                . HtmlGenerator::createTag(
                    'div',
                    HtmlGenerator::createTag('table')
                        ->setClass(trim(self::$CLASSES['table'] . ' ' . $this->table->getClass()))
                        ->setContent(
                            $this->renderTableHeader()
                            . $this->renderTableBody()
                        )
                )->setClass(self::$CLASSES['table-div'])
                . HtmlGenerator::createTag('div')
                    ->setClass(trim('table-footer ' . self::$CLASSES['table-footer']))
                    ->setContent($this->renderFooter())
            )
            ->setId($this->table->getId() ?? 'tableId')
            ->setClass(trim('table-module ' . self::$CLASSES['main-div']))
            ->addAttribute('data-url', '/' . (new \NimblePHP\Framework\Request())->getQuery('url'));
    }

    /**
     * Render footer
     * @return string
     */
    private function renderFooter(): string
    {
        return HtmlGenerator::createTag('div')
            ->setClass('d-flex justify-content-between')
            ->setContent(
                HtmlGenerator::createTag('div')
                    ->setContent($this->renderFooterFilters())
                . HtmlGenerator::createTag('div')
                    ->setContent($this->renderFooterPagination())
            );
    }

    /**
     * Render footer filters
     * @return string
     */
    private function renderFooterFilters(): string
    {
        $content = '';

        /** @var Filter $filter */
        foreach ($this->table->getFilters() as $filter) {
            $content .= $filter->render($this->table);
        }

        return $content;
    }

    /**
     * Render footer pagination
     * @return string
     */
    private function renderFooterPagination(): string
    {
        $content = '';

        if ($this->table->isAjax() && $this->table->getPageCount() > 1) {
            $pages = '';
            $start = max(1, $this->table->getPage() - 3);
            $end = min($this->table->getPageCount(), $this->table->getPage() + 3);

            for ($i = $start; $i <= $end; $i++) {
                $pages .= HtmlGenerator::createTag('li')
                    ->setClass(trim('page-item ' . ($this->table->getPage() === $i ? 'active' : '')))
                    ->setContent(
                        HtmlGenerator::createTag('a')
                            ->setClass(trim('page-link ajax-link'))
                            ->addAttribute('href', '?page=' . $i)
                            ->setContent($i)
                    );
            }

            $content .= HtmlGenerator::createTag('div')
                ->setContent(
                    HtmlGenerator::createTag('nav')
                        ->setClass('table-footer-pagination')
                        ->setContent(
                            HtmlGenerator::createTag('ul')
                                ->setClass(trim('pagination ' . self::$CLASSES['table-footer-pagination-ul']))
                                ->setContent(
                                    ($this->table->getPage() > 1
                                        ? HtmlGenerator::createTag('li')
                                            ->setClass(self::$CLASSES['table-footer-pagination-li'])
                                            ->setContent(
                                                HtmlGenerator::createTag('a')
                                                    ->setClass(trim('ajax-link ' . self::$CLASSES['table-footer-pagination-a']))
                                                    ->addAttribute('href', '?page=' . $this->table->getPage() - 1)
                                                    ->setContent('&laquo;')
                                            )
                                        : '')
                                    . $pages
                                    . ($this->table->getPage() < $this->table->getPageCount()
                                        ? HtmlGenerator::createTag('li')
                                            ->setClass('page-item')
                                            ->setContent(
                                                HtmlGenerator::createTag('a')
                                                    ->setClass('page-link ajax-link')
                                                    ->addAttribute('href', '?page=' . $this->table->getPage() + 1)
                                                    ->setContent('&raquo;')
                                            )
                                        : '')
                                )
                        )
                );
        }

        return $content;
    }

    /**
     * Render table body
     * @return string
     * @throws \NimblePHP\Framework\Exception\DatabaseException
     */
    private function renderTableBody(): string
    {
        $content = '';

        foreach ($this->table->getData() as $data) {
            $trContent = '';

            foreach ($this->table->getColumns() as $column) {
                $trContent .= HtmlGenerator::createTag('td')
                    ->addAttribute('style', $column->getStyleAsString())
                    ->setContent($this->table->prepareColumnValue($column, $data));
            }

            $content .= HtmlGenerator::createTag('tr')
                ->setContent($trContent);
        }

        return HtmlGenerator::createTag('tbody')
            ->setClass(self::$CLASSES['table-tbody'])
            ->setContent($content);
    }

    /**
     * Render table header
     * @return string
     */
    private function renderTableHeader(): string
    {
        $headContent = '';

        /** @var Column $column */
        foreach ($this->table->getColumns() as $column) {
            $headContent .= HtmlGenerator::createTag('th')
                ->addAttribute('scope', 'col')
                ->addAttribute('style', trim($column->getStyleAsString()))
                ->setContent($column->getName());
        }

        return HtmlGenerator::createTag('thead')
            ->setClass(trim(self::$CLASSES['table-thead']))
            ->setContent(
                HtmlGenerator::createTag('tr')
                    ->setClass(trim(self::$CLASSES['table-thead-tr']))
                    ->setContent($headContent)
            );
    }

    /**
     * Render header
     * @return string
     */
    private function renderHeader(): string
    {
        $content = '';

        if ($this->table->isAjax()) {
            $actionContent = '';

            foreach ($this->table->getActions() as $action) {
                $actionContent .= HtmlGenerator::createTag('a')
                    ->addAttribute('href', $action['url'])
                    ->setClass(trim(self::$CLASSES['action-button'] . ' ' . $action['class']))
                    ->setContent($action['name']);
            }

            $content .= HtmlGenerator::createTag('div')
                ->setClass(trim('action-div ' . self::$CLASSES['action-div']))
                ->setContent($actionContent);

            $content .= HtmlGenerator::createTag('div')
                ->setclass(trim(self::$CLASSES['action-search-div']))
                ->setContent(
                    HtmlGenerator::createTag('input')
                        ->setClass(trim('ajax-form ' . self::$CLASSES['action-search-input']))
                        ->addAttribute('type', 'search')
                        ->addAttribute('name', 'search')
                        ->addAttribute('value', $this->table->getSearch())
                        ->addAttribute('placeholder', Table::$LANGUAGE['search'])
                        ->addAttribute('aria-label', 'Search')
                );
        }

        return $content;
    }

}