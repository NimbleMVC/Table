<?php

namespace NimblePHP\Table;

use krzysztofzylka\DatabaseManager\Condition;
use krzysztofzylka\DatabaseManager\Exception\DatabaseManagerException;
use NimblePHP\Framework\Cookie;
use NimblePHP\Framework\Exception\DatabaseException;
use NimblePHP\Framework\Exception\NimbleException;
use NimblePHP\Framework\Interfaces\ModelInterface;
use NimblePHP\Framework\ModuleRegister;
use NimblePHP\Framework\Request;
use NimblePHP\Table\Interfaces\ColumnInterface;
use NimblePHP\Table\Interfaces\FilterInterface;
use NimblePHP\Table\Interfaces\TableInterface;
use NimblePHP\Table\Template\Template;

/**
 * Initialize table
 */
class Table implements TableInterface
{

    /**
     * Language
     * @var array|string[]
     */
    public static array $LANGUAGE = [
        'search' => 'Search...',
        'show' => 'Show',
        'records' => 'records',
        'page' => 'Page',
        'of' => 'of',
        'empty_data' => 'No data to display'
    ];

    /**
     * Layout
     * empty (normal, professional, modern)
     * @var string
     */
    public static string $layout = 'normal';

    /**
     * Current table layout
     * @var string|null
     */
    public ?string $currentLayout = null;

    /**
     * Columns
     * @var array
     */
    protected array $columns = [];

    /**
     * Data
     * @var ?array
     */
    protected ?array $data = null;

    /**
     * Table id
     * @var string|null
     */
    protected ?string $id = null;

    /**
     * Model
     * @var ?ModelInterface
     */
    protected ?ModelInterface $model;

    /**
     * Conditions
     * @var array
     */
    protected array $conditions = [];

    /**
     * Use ajax
     * @var bool
     */
    protected bool $ajax = false;

    /**
     * Ajax action
     * @var bool
     */
    protected bool $ajaxAction = false;

    /**
     * Ajax action key
     * @var string|null
     */
    protected ?string $ajaxActionKey = null;

    /**
     * Limit
     * @var int
     */
    protected int $limit = 15;

    /**
     * Page
     * @var int
     */
    protected int $page = 1;

    /**
     * Page count
     * @var int
     */
    protected int $pageCount = 1;

    /**
     * Zliczenie ilości danych
     * @var int
     */
    protected int $dataCount = 0;

    /**
     * Group by
     * @var ?string
     */
    protected ?string $groupBy = null;

    /**
     * Request instance
     * @var Request
     */
    protected Request $request;

    /**
     * Cookie instance
     * @var Cookie
     */
    protected Cookie $cookie;

    /**
     * Config name
     * @var ?string
     */
    protected ?string $configName = null;

    /**
     * Table client configuration
     * @var array
     */
    protected array $config = [];

    /**
     * Search string
     * @var string
     */
    protected string $search = '';

    /**
     * Actions
     * @var array
     */
    protected array $actions = [];


    /**
     * Order by
     * @var ?string
     */
    protected ?string $orderBy = null;

    /**
     * Additional table class
     * @var string
     */
    protected string $class = '';

    /**
     * Is ajax table
     * @var bool
     */
    protected bool $isAjax = false;

    /**
     * Ajax database key
     * @var null|int|string
     */
    protected static null|int|string $ajaxKey = null;

    /**
     * Config table
     * @var \krzysztofzylka\DatabaseManager\Table
     */
    protected \krzysztofzylka\DatabaseManager\Table $configTable;

    /**
     * Filters
     * @var array
     */
    protected array $filters = [];

    /**
     * Column is sortable
     * @var bool
     */
    protected bool $sortable = true;

    /**
     * Sort column
     * @var array
     */
    protected array $sortColumn = [];

    /**
     * Base order by
     * @var string|null
     */
    protected ?string $baseOrderBy = null;

    /**
     * Initialize
     */
    public function __construct(?string $id = null)
    {
        if (ModuleRegister::isset('nimblephp/migrations') && $_ENV['DATABASE']) {
            $this->configTable = new \krzysztofzylka\DatabaseManager\Table('module_table_config');
        }

        $this->request = new Request();
        $this->cookie = new Cookie();

        if (!is_null($id)) {
            $this->setId($id, false);
        }
    }

    /**
     * Change language
     * @param string $lang
     * @return void
     */
    public static function changeLanguage(string $lang): void
    {
        if ($lang === 'pl') {
            self::$LANGUAGE = [
                'search' => 'Wyszukaj...',
                'show' => 'Pokaż',
                'records' => 'rekordów',
                'page' => 'Strona',
                'of' => 'z',
                'empty_data' => 'Brak danych do wyświetlenia'
            ];
        }
    }

    /**
     * Set sortable
     * @param bool $sortable
     * @return TableInterface
     */
    public function setSortable(bool $sortable): TableInterface
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
     * Save config
     * @return void
     */
    public function saveConfig(): void
    {
        if ($this->isAjax()) {
            return;
        }

        $this->cookie->set($this->configName, json_encode($this->config));
    }

    /**
     * Set config
     * @return void
     */
    public function readConfig(): void
    {
        if ($this->isAjax()) {
            return;
        }

        if ($this->cookie->exists($this->configName)) {
            $this->config = $this->config + json_decode($this->cookie->get($this->configName), true);
        }

        if (isset($this->config['page'])) {
            $this->setPage($this->config['page']);
        }

        if (isset($this->config['search'])) {
            $this->setSearch($this->config['search']);
        }
    }

    /**
     * Query config
     * @return void
     */
    public function queryConfig(): void
    {
        $this->config['page'] = $this->request->getQuery('page', $this->getPage());
        $this->config['search'] = $this->request->getQuery('search', $this->getSearch());
    }

    /**
     * Add column
     * @param ColumnInterface $column
     * @return void
     */
    public function addColumn(ColumnInterface $column): void
    {
        $this->columns[$column->getKey()] = $column;
    }

    /**
     * Add data
     * @param array $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Get data
     * @return array
     * @throws DatabaseException
     */
    public function getData(): array
    {
        if ($this->data === null && isset($this->model) && $this->model instanceof ModelInterface) {
            $this->data = $this->model->readAll(
                $this->getConditions(),
                null,
                $this->getOrderBy(),
                (($this->getPage() - 1) * $this->getLimit()) . ',' . $this->getLimit(),
                $this->getGroupBy()
            );
        }

        return $this->data ?? [];
    }

    /**
     * Render table
     * @return string
     * @throws DatabaseException
     * @throws DatabaseManagerException
     * @throws NimbleException
     */
    public function render(): string
    {
        $isSaveAjaxMode = $this->isAjax()
            && $this->request->getPost('table_action_id', false) === $this->getId();
        $this->baseOrderBy = $this->getOrderBy();

        if ($this->isAjax()) {
            if (!$_ENV['DATABASE']) {
                throw new NimbleException('The database must be enabled', 500);
            }

            if (!ModuleRegister::isset('nimblephp/migrations')) {
                throw new NimbleException('The nimblephp/migrations module must be enabled', 500);
            }

            $this->getAjaxConfig();

            if ($isSaveAjaxMode) {
                $this->saveAjaxConfig();
            }
        }

        foreach ($this->filters as $filter) {
            $this->conditions = array_merge($this->conditions, $filter->getCondition());
        }

        if ($this->hasAjaxAction()) {
            $this->generateAjaxAction();
        }

        ob_start();

        $this->prepareDataCount();
        $template = new Template($this, $this->currentLayout ?? self::$layout);

        if ($isSaveAjaxMode) {
            ob_clean();
            echo $template->render();
            exit;
        }

        return $template->render();
    }

    /**
     * Save ajax config
     * @return void
     * @throws DatabaseManagerException
     */
    public function saveAjaxConfig(): void
    {
        if (!$this->isAjax()) {
            return;
        }

        $sortColumnData = [];
        $filters = [];

        $page = $this->request->getPost('page');
        $search = $this->request->getPost('search');
        $sortColumn = $this->request->getPost('sort_column_key');

        if (!is_null($page)) {
            $this->setPage($page);
        }

        if (!is_null($search)) {
            $this->setSearch($search);
        }

        foreach ($this->request->getAllPost() as $key => $value) {
            if (str_starts_with($key, 'filter-')) {
                $explode = explode('-', $key);

                if (array_key_exists($explode[1], $this->getFilters())) {
                    /** @var FilterInterface $filter */
                    $filter = $this->getFilters()[$explode[1]];
                    $filter->setValue($value);
                    $filters[$explode[1]] = $filter->getValue();
                }
            }
        }

        if (!is_null($sortColumn)) {
            $sort = $this->request->getPost('sort_column_direction');

            if ($sort === 'none') {
                $sortColumnData = [
                    'key' => $sortColumn,
                    'direction' => 'asc'
                ];
            } elseif ($sort === 'asc') {
                $sortColumnData = [
                    'key' => $sortColumn,
                    'direction' => 'desc'
                ];
            }

            $this->setSortColumn($sortColumnData);

            if (empty($sortColumnData)) {
                $this->setOrderBy($this->baseOrderBy);
            } else {
                $this->setOrderBy(htmlspecialchars($sortColumnData['key'] . ' ' . $sortColumnData['direction']));
            }
        }

        $config = [
            'page' => $this->getPage(),
            'search' => $this->getSearch(),
            'orderBy' => $this->getOrderBy(),
            'limit' => $this->getLimit(),
            'filters' => $filters,
            'sortColumn' => $sortColumnData ?: $this->getSortColumn()
        ];

        $find = $this->configTable->find(
            [
                'module_table_config.table_id' => $this->getId(),
                'module_table_config.key' => $this->getAjaxKey()
            ],
            ['module_table_config.id']
        );

        if ($find) {
            $this->configTable->setId($find['module_table_config']['id'])->update(['config' => json_encode($config)]);
        } else {
            $this->configTable->insert(
                [
                    'key' => $this->getAjaxKey(),
                    'table_id' => $this->getId(),
                    'config' => json_encode($config)
                ]
            );
        }

        $this->configTable->setId();
    }

    /**
     * Get ajax config
     * @return void
     * @throws DatabaseManagerException
     */
    public function getAjaxConfig(): void
    {
        if (!$this->isAjax()) {
            return;
        }

        $find = $this->configTable->find(
            [
                'module_table_config.table_id' => $this->getId(),
                'module_table_config.key' => $this->getAjaxKey()
            ],
            ['module_table_config.config']
        );

        if ($find) {
            $config = json_decode($find['module_table_config']['config'], true);

            $this->setPage($config['page']);
            $this->setSearch($config['search']);

            if (!is_null($config['orderBy'])) {
                $this->setOrderBy($config['orderBy']);
            }

            $this->setLimit($config['limit']);

            foreach ($config['filters'] ?? [] as $filterKey => $value) {
                if (array_key_exists($filterKey, $this->filters)) {
                    /** @var FilterInterface $filter */
                    $filter = $this->filters[$filterKey];
                    $filter->setValue($value ?? '');
                }
            }

            if (!empty($config['sortColumn'])) {
                $this->setSortColumn($config['sortColumn']);
                $this->setOrderBy(htmlspecialchars($config['sortColumn']['key'] . ' ' . $config['sortColumn']['direction']));
            } elseif (empty($config['sortColumn']) && !empty($this->baseOrderBy)) {
                $this->setsortColumn([]);
                $this->setOrderBy($this->baseOrderBy);
            }
        }
    }

    /**
     * Prepare column value
     * @param ColumnInterface $column
     * @param array $data
     * @return mixed
     */
    public function prepareColumnValue(ColumnInterface $column, array $data): string
    {
        if (is_callable($column->getValue())) {
            $cell = new Cell();
            $cell->value = $this->getDataFromArray($data, $column->getKey()) ?? '';
            $cell->data = $data;

            return $column->getValue()($cell);
        } elseif (!is_null($column->getValue())) {
            return $column->getValue();
        }

        return $this->getDataFromArray($data, $column->getKey()) ?? '';
    }

    /**
     * Get table id
     * @return ?string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set table id
     * @param ?string $id
     * @param bool $config
     * @return void
     */
    public function setId(?string $id, bool $config = true): void
    {
        $this->id = $id;

        $this->configName = $id ? "table_{$id}_config" : '';

        if ($config) {
            $this->readConfig();
            $this->queryConfig();
            $this->readConfig();
            $this->saveConfig();
        }
    }

    /**
     * Get table model
     * @return ?ModelInterface
     */
    public function getModel(): ?ModelInterface
    {
        return $this->model;
    }

    /**
     * Set table model
     * @param ?ModelInterface $model
     * @return void
     */
    public function setModel(?ModelInterface $model): void
    {
        $this->ajax = !is_null($model);
        $this->model = $model;
    }

    /**
     * Get conditions
     * @return array
     */
    public function getConditions(): array
    {
        if (!empty($this->getSearch())) {
            $searchConditions = ['OR' => []];

            /** @var ColumnInterface $column */
            foreach ($this->columns as $column) {
                if ($column->getSearch()) {
                    $searchConditions['OR'][] = new Condition($column->getKey(), 'LIKE', '%' . $this->getSearch() . '%');
                }
            }

            $this->conditions[] = $searchConditions;
        }

        return $this->conditions;
    }

    /**
     * Set conditions
     * @param array $conditions
     * @return void
     */
    public function setConditions(array $conditions): void
    {
        $this->conditions = $conditions;
    }

    /**
     * Get limit
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Set limit
     * @param int $limit
     * @return void
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * Get actual page
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Set actual page
     * @param int $page
     * @return void
     */
    public function setPage(int $page): void
    {
        $this->page = max($page, 1);
    }

    /**
     * Get page count
     * @return int
     */
    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    /**
     * Set page count
     * @param int $pageCount
     * @return void
     */
    public function setPageCount(int $pageCount): void
    {
        $this->pageCount = $pageCount;
    }

    /**
     * Get group by
     * @return ?string
     */
    public function getGroupBy(): ?string
    {
        return $this->groupBy;
    }

    /**
     * Set group by
     * @param ?string $groupBy
     * @return void
     */
    public function setGroupBy(?string $groupBy): void
    {
        $this->groupBy = $groupBy;
    }

    /**
     * Get search
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * Set search
     * @param string $search
     * @return $this
     */
    public function setSearch(string $search): self
    {
        $this->search = $search;

        return $this;
    }

    /**
     * Get order by
     * @return ?string
     */
    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    /**
     * Set order by
     * @param ?string $orderBy
     * @return $this
     */
    public function setOrderBy(?string $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * Add action
     * @param string $name
     * @param string $url
     * @param string $class
     * @return $this
     */
    public function addAction(string $name, string $url, string $class = '', bool $ajaxAction = false): self
    {
        if ($ajaxAction) {
            $class .= ' ajax-action-button';
        }

        $this->actions[] = [
            'name' => $name,
            'url' => $url,
            'class' => $class
        ];

        return $this;
    }

    /**
     * Get actions
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Get columns
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Set additional table class
     * @param string $class
     * @return void
     */
    public function setClass(string $class = ''): void
    {
        $this->class = $class;
    }

    /**
     * Get additional table class
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Count data
     * @return void
     * @throws DatabaseException
     */
    protected function prepareDataCount(): void
    {
        if ($this->data === null && isset($this->model) && $this->model instanceof ModelInterface) {
            $this->dataCount = $this->getModel()->count(
                $this->getConditions()
            );
        } else {
            $this->dataCount = count($this->data ?? []);
        }

        $this->setPageCount(ceil($this->dataCount / $this->getLimit()));

        if ($this->getPage() > $this->getPageCount()) {
            $this->setPage($this->getPageCount());
        }
    }

    /**
     * Get data from array by key
     * @param array $data
     * @param string $key
     * @return string|null
     */
    protected function getDataFromArray(array $data, string $key): ?string
    {
        try {
            $generatedArray = '["' . implode('"]["', explode('.', $key)) . '"]';

            return @eval('return isset($data' . $generatedArray . ') ? $data' . $generatedArray . ' : \'\' ;');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Is ajax
     * @return bool
     */
    public function isAjax(): bool {
        return $this->ajax;
    }

    /**
     * Set ajax mode
     * @param bool $ajax
     * @return Table
     */
    public function setAjax(bool $ajax = true): self
    {
        $this->ajax = $ajax;

        return $this;
    }

    /**
     * Set ajax key
     * @param int|string|null $ajaxKey
     */
    public static function setAjaxKey(null|int|string $ajaxKey): void
    {
        self::$ajaxKey = $ajaxKey;
    }

    /**
     * Get ajax key
     * @return null|int|string
     */
    public function getAjaxKey(): null|int|string
    {
        return self::$ajaxKey;
    }

    /**
     * Add filter
     * @param FilterInterface $filter
     * @return Table
     */
    public function addFilter(FilterInterface $filter): self
    {
        $this->filters[$filter->getKey()] = $filter;

        return $this;
    }

    /**
     * Get filters
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Set current table layout
     * @param string $layout
     * @return void
     */
    public function setLayout(string $layout): void
    {
        $this->currentLayout = $layout;
    }

    /**
     * Set ajax action
     * @param bool $ajaxAction
     * @return self
     */
    public function setAjaxAction(bool $ajaxAction = true): self
    {
        $this->ajaxAction = $ajaxAction;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAjaxAction(): bool
    {
        return $this->ajaxAction;
    }

    /**
     * Set ajax action key
     * @param ?string $key
     * @return $this
     */
    public function setAjaxActionKey(?string $key): self
    {
        $this->ajaxActionKey = $key;

        return $this;
    }

    /**
     * Get ajax action key
     * @return array
     */
    public function getAjaxActionKey(): array
    {
        if (is_null($this->ajaxActionKey)) {
            $this->setAjaxActionKey($this->model->getTableInstance()->getName() . '.id');
        }

        return explode('.', $this->ajaxActionKey, 2);
    }

    /**
     * Set sort column
     * @param array $sortColumn
     * @return TableInterface
     */
    public function setSortColumn(array $sortColumn): TableInterface
    {
        $this->sortColumn = $sortColumn;

        return $this;
    }

    /**
     * Get sort column
     * @return array
     */
    public function getSortColumn(): array
    {
        return $this->sortColumn;
    }

    /**
     * Generate ajax action
     * @return void
     */
    private function generateAjaxAction(): void
    {
        $width = '30px';
        $actionColumn = Column::create(':action_checkbox_ajax:', '')
            ->setStyle(['width' => $width, 'min-width' => $width, 'padding-right' => '10px', 'padding-top' => '10px', 'position' => 'relative'])
            ->setSearch(false)
            ->setSortable(false)
            ->setValue(function (Cell $cell) {
                return '<input type="checkbox" style="position: absolute; top: 12px;" class="ajax-action-checkbox" value="' . $cell->data[$this->getAjaxActionKey()[0]][$this->getAjaxActionKey()[1]] . '" />';
            });

        $this->columns = [$actionColumn] + $this->columns;
    }

}