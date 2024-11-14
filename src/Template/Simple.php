<?php

namespace Nimblephp\table\Template;

use Krzysztofzylka\HtmlGenerator\HtmlGenerator;
use Nimblephp\table\Column;
use Nimblephp\table\Table;

class Simple
{

    public Table $table;

    public static array $CLASSES = [
        'main-div' => 'm-2',
        'header' => 'row position-relative',
        'table' => 'table table-striped m-0',
        'table-thead' => '',
        'table-thead-tr' => '',
        'table-tbody' => '',
        'action-div' => 'col position-absolute bottom-0 pb-1',
        'action-button' => 'btn btn-primary',
        'action-search-div' => 'col d-flex justify-content-end',
        'action-search-form' => 'd-flex',
        'action-search-input' => 'form-control me-2',
        'table-footer' => 'p-1 mt-2',
        'table-footer-pagination-ul' => 'justify-content-end m-0'
    ];

    /**
     * Render table
     * @param Table $table
     * @return string
     * @throws \Nimblephp\framework\Exception\DatabaseException
     */
    public function render(Table $table): string
    {
        $this->table = $table;

        return HtmlGenerator::createTag('div')
            ->setContent(
                HtmlGenerator::createTag('div')
                    ->setClass(trim('table-header ' . self::$CLASSES['table']))
                    ->setContent($this->renderHeader())
                . HtmlGenerator::createTag('table')
                    ->setClass(trim(self::$CLASSES['table'] . ' ' . $this->table->getClass()))
                    ->setContent(
                        $this->renderTableHeader()
                        . $this->renderTableBody()
                    )
                . HtmlGenerator::createTag('div')
                    ->setClass(trim('table-footer ' . self::$CLASSES['table-footer']))
                    ->setContent($this->renderFooter())
            )
            ->setId($this->table->getId())
            ->setClass(trim('table-module ' . self::$CLASSES['main-div']));
    }

    /**
     * Render footer
     * @return string
     */
    private function renderFooter(): string
    {
        return $this->renderFooterPagination();
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
                                            ->setClass('page-item')
                                            ->setContent(
                                                HtmlGenerator::createTag('a')
                                                ->setClass('page-link ajax-link')
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
     * @throws \Nimblephp\framework\Exception\DatabaseException
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
                ->setClass(trim('action-div'))
                ->setContent($actionContent);

            $content .= HtmlGenerator::createTag('div')
                ->setclass(trim(self::$CLASSES['action-search-div']))
                ->setContent(
                    HtmlGenerator::createTag('form')
                        ->addAttribute('onsubmit', 'handleSubmit()')
                        ->setClass(trim(self::$CLASSES['action-search-form']))
                        ->setContent(
                            HtmlGenerator::createTag('input')
                                ->setClass(trim('ajax-form ' . self::$CLASSES['action-search-input']))
                                ->addAttribute('type', 'search')
                                ->addAttribute('name', 'search')
                                ->addAttribute('value', $this->table->getSearch())
                                ->addAttribute('placeholder', 'Search...')
                                ->addAttribute('aria-label', 'Search')
                        )
                );
        }

        return $content;
    }

}