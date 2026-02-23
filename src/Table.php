<?php

namespace NimblePHP\Table;

use krzysztofzylka\DatabaseManager\Condition;
use krzysztofzylka\DatabaseManager\Exception\DatabaseManagerException;
use NimblePHP\Framework\Cookie;
use NimblePHP\Framework\Exception\DatabaseException;
use NimblePHP\Framework\Exception\NimbleException;
use NimblePHP\Framework\Exception\ValidationException;
use NimblePHP\Framework\Interfaces\ModelInterface;
use NimblePHP\Framework\Module\ModuleRegister;
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
     * Columns to read
     * @var array|null
     */
    protected ?array $readColumns = null;

    /**
     * Inline edit errors
     * @var array
     */
    protected array $inlineEditErrors = [];

    /**
     * Inline edit values
     * @var array
     */
    protected array $inlineEditValues = [];

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
                $this->getReadColumns(),
                $this->getOrderBy(),
                (($this->getPage() - 1) * $this->getLimit()) . ',' . $this->getLimit(),
                $this->getGroupBy()
            );
        }

        return $this->data ?? [];
    }

    /**
     * Get read columns
     * @return array|null
     */
    public function getReadColumns(): ?array
    {
        if ($this->readColumns === null) {
            return $this->readColumns;
        }

        return array_unique($this->readColumns);
    }

    /**
     * @param array|null $readColumns
     * @return void
     */
    public function setReadColumns(?array $readColumns): void
    {
        $this->readColumns = $readColumns;
    }

    /**
     * @param string|null ...$columName
     * @return void
     */
    public function addReadColumn(?string ...$columName): void
    {
        if ($this->readColumns === null) {
            $this->readColumns = [];
        }

        foreach ($columName as $column) {
            $this->readColumns[] = $column;
        }
    }

    /**
     * @return void
     */
    public function autoReadColumns(): void
    {
        $this->readColumns = [
            $this->model->getTableInstance()->getName() . '.id'
        ];

        /** @var ColumnInterface $column */
        foreach ($this->columns as $column) {
            if (!$column->getSearch()) {
                continue;
            }

            $this->readColumns[] = $column->getKey();
        }
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

        /**
         * Dynamiczny wybór źródła konfiguracji:
         * - AJAX => DB
         * - non-AJAX => cookies
         */
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
        } else {
            // Fallback do cookies tylko wtedy, gdy nie używamy DB-config
            $this->readConfig();
            $this->queryConfig();
            $this->readConfig();
            $this->saveConfig();
        }

        foreach ($this->filters as $filter) {
            $this->conditions = array_merge($this->conditions, $filter->getCondition());
        }

        if ($this->hasAjaxAction()) {
            $this->generateAjaxAction();
        }

        $this->processInlineEditUpdate($isSaveAjaxMode);

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

        /**
         * @var string $key
         * @var FilterInterface $filter
         */
        foreach ($this->filters as $key => $filter) {
            if (is_null($filter->getValue())) {
                continue;
            }

            $filters[$key] = $filter->getValue();
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
            return $column->getValue()($this->createCell($column, $data));
        } elseif (!is_null($column->getValue())) {
            return $column->getValue();
        }

        return $this->getDataFromArray($data, $column->getKey()) ?? '';
    }

    /**
     * Prepare column edit value
     * @param ColumnInterface $column
     * @param array $data
     * @return string
     */
    public function prepareColumnEditValue(ColumnInterface $column, array $data): string
    {
        if (!is_callable($column->getEdit())) {
            return '';
        }

        $cell = $this->createCell($column, $data);
        $submittedValue = $this->getInlineEditValueByRow($column, $data);

        if (!is_null($submittedValue)) {
            $cell->value = $submittedValue;
        }

        return $column->getEdit()($cell);
    }

    /**
     * Is column editable
     * @param ColumnInterface $column
     * @return bool
     */
    public function isColumnEditable(ColumnInterface $column): bool
    {
        return $column->isEditable();
    }

    /**
     * Get inline edit error by row
     * @param ColumnInterface $column
     * @param array $data
     * @return string|null
     */
    public function getInlineEditErrorByRow(ColumnInterface $column, array $data): ?string
    {
        $rowId = $this->getRowIdentifier($data);

        if (is_null($rowId)) {
            return null;
        }

        return $this->getInlineEditError($column->getKey(), $rowId);
    }

    /**
     * Has inline edit error by row
     * @param ColumnInterface $column
     * @param array $data
     * @return bool
     */
    public function hasInlineEditErrorByRow(ColumnInterface $column, array $data): bool
    {
        return !is_null($this->getInlineEditErrorByRow($column, $data));
    }

    /**
     * Get inline edit value by row
     * @param ColumnInterface $column
     * @param array $data
     * @return mixed
     */
    public function getInlineEditValueByRow(ColumnInterface $column, array $data): mixed
    {
        $rowId = $this->getRowIdentifier($data);

        if (is_null($rowId)) {
            return null;
        }

        return $this->getInlineEditValue($column->getKey(), $rowId);
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
     * Get row identifier
     * @param array $data
     * @return null|int|string
     */
    public function getRowIdentifier(array $data): null|int|string
    {
        if (!isset($this->model) || is_null($this->model)) {
            return null;
        }

        $key = implode('.', $this->getAjaxActionKey());
        $id = $this->getDataFromArray($data, $key);

        if (is_null($id) || $id === '') {
            return null;
        }

        return ctype_digit((string)$id)
            ? (int)$id
            : $id;
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
     * Process inline edit update
     * @param bool $isSaveAjaxMode
     * @return void
     */
    protected function processInlineEditUpdate(bool $isSaveAjaxMode): void
    {
        if (!$isSaveAjaxMode) {
            return;
        }

        $columnKey = (string)$this->request->getPost('table_edit_column', '', false);
        $rowId = $this->request->getPost('table_edit_id', null, false);

        if ($columnKey === '' || is_null($rowId) || $rowId === '') {
            return;
        }

        $column = $this->columns[$columnKey] ?? null;

        if (!$column instanceof ColumnInterface || !$column->hasOnUpdate()) {
            return;
        }

        $value = $this->request->getPost('table_edit_value', null, false);

        if (is_null($value)) {
            foreach ($this->request->getAllPost(false) as $postKey => $postValue) {
                if (in_array($postKey, ['table_action_id', 'table_edit_column', 'table_edit_id', 'table_edit_value'], true)) {
                    continue;
                }

                $value = $postValue;
                break;
            }
        }

        $preparedRowId = ctype_digit((string)$rowId)
            ? (int)$rowId
            : $rowId;

        try {
            $column->getOnUpdate()($preparedRowId, $value);
            $this->clearInlineEditState($columnKey, $preparedRowId);
        } catch (ValidationException $exception) {
            $this->setInlineEditValue($columnKey, $preparedRowId, $value);
            $this->setInlineEditError($columnKey, $preparedRowId, $exception->getMessage());
        }
    }

    /**
     * Set inline edit error
     * @param string $columnKey
     * @param int|string $rowId
     * @param string $message
     * @return void
     */
    protected function setInlineEditError(string $columnKey, int|string $rowId, string $message): void
    {
        $this->inlineEditErrors[$this->buildInlineEditMapKey($columnKey, $rowId)] = $message;
    }

    /**
     * Get inline edit error
     * @param string $columnKey
     * @param int|string $rowId
     * @return string|null
     */
    protected function getInlineEditError(string $columnKey, int|string $rowId): ?string
    {
        return $this->inlineEditErrors[$this->buildInlineEditMapKey($columnKey, $rowId)] ?? null;
    }

    /**
     * Set inline edit value
     * @param string $columnKey
     * @param int|string $rowId
     * @param mixed $value
     * @return void
     */
    protected function setInlineEditValue(string $columnKey, int|string $rowId, mixed $value): void
    {
        $this->inlineEditValues[$this->buildInlineEditMapKey($columnKey, $rowId)] = $value;
    }

    /**
     * Get inline edit value
     * @param string $columnKey
     * @param int|string $rowId
     * @return mixed
     */
    protected function getInlineEditValue(string $columnKey, int|string $rowId): mixed
    {
        return $this->inlineEditValues[$this->buildInlineEditMapKey($columnKey, $rowId)] ?? null;
    }

    /**
     * Clear inline edit state
     * @param string $columnKey
     * @param int|string $rowId
     * @return void
     */
    protected function clearInlineEditState(string $columnKey, int|string $rowId): void
    {
        $mapKey = $this->buildInlineEditMapKey($columnKey, $rowId);

        unset($this->inlineEditErrors[$mapKey], $this->inlineEditValues[$mapKey]);
    }

    /**
     * Build inline edit map key
     * @param string $columnKey
     * @param int|string $rowId
     * @return string
     */
    protected function buildInlineEditMapKey(string $columnKey, int|string $rowId): string
    {
        return $columnKey . '::' . (string)$rowId;
    }

    /**
     * Create cell object for callback
     * @param ColumnInterface $column
     * @param array $data
     * @return Cell
     */
    protected function createCell(ColumnInterface $column, array $data): Cell
    {
        $cell = new Cell();
        $cell->value = $this->getDataFromArray($data, $column->getKey()) ?? '';
        $cell->data = $data;

        return $cell;
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
        $actionColumn = Column::create(':action_checkbox_select_all:', '<input type="checkbox" style="position: absolute; top: 12px;" class="action-checkbox-select-all" />')
            ->setStyle(['width' => $width, 'min-width' => $width, 'padding-right' => '10px', 'padding-top' => '10px', 'position' => 'relative'])
            ->setSearch(false)
            ->setSortable(false)
            ->setValue(function (Cell $cell) {
                return '<input type="checkbox" style="position: absolute; top: 12px;" class="ajax-action-checkbox" value="' . $cell->data[$this->getAjaxActionKey()[0]][$this->getAjaxActionKey()[1]] . '" />';
            });

        $this->columns = [$actionColumn] + $this->columns;
    }

}