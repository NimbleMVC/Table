# Klasa Table

Klasa `Table` jest główną klasą biblioteki NimblePHP Table, implementującą interfejs `TableInterface`. Umożliwia tworzenie interaktywnych tabel z funkcjami sortowania, filtrowania, paginacji i obsługą AJAX.

## Namespace

```php
NimblePHP\Table\Table
```

## Właściwości statyczne

### `$LANGUAGE`
```php
public static array $LANGUAGE
```
Tablica z tłumaczeniami interfejsu tabeli.

**Domyślne wartości:**
```php
[
    'search' => 'Search...',
    'show' => 'Show',
    'records' => 'records',
    'page' => 'Page',
    'of' => 'of',
    'empty_data' => 'No data to display'
]
```

### `$layout`
```php
public static string $layout = 'normal'
```
Globalny szablon dla wszystkich tabel. Dostępne opcje: `'normal'`, `'professional'`, `'modern'`.

## Konstruktor

### `__construct(?string $id = null)`

Tworzy nową instancję tabeli.

**Parametry:**
- `$id` (string|null) - Unikalny identyfikator tabeli

**Przykład:**
```php
$table = new Table('users-table');
```

## Metody statyczne

### `changeLanguage(string $lang): void`

Zmienia język interfejsu tabeli.

**Parametry:**
- `$lang` (string) - Kod języka ('pl' dla polskiego)

**Przykład:**
```php
Table::changeLanguage('pl');
```

### `setAjaxKey(null|int|string $ajaxKey): void`

Ustawia klucz AJAX używany do rozróżniania konfiguracji użytkowników.

**Parametry:**
- `$ajaxKey` (null|int|string) - Klucz AJAX

**Przykład:**
```php
Table::setAjaxKey(session_id());
```

## Metody zarządzania danymi

### `setData(array $data): void`

Ustawia dane tabeli jako tablicę.

**Parametry:**
- `$data` (array) - Tablica danych

**Przykład:**
```php
$table->setData([
    ['id' => 1, 'name' => 'Jan', 'email' => 'jan@example.com'],
    ['id' => 2, 'name' => 'Anna', 'email' => 'anna@example.com']
]);
```

### `getData(): array`

Pobiera dane tabeli. Jeśli ustawiony jest model, dane są pobierane automatycznie z bazy danych.

**Zwraca:** array - Tablica danych

**Przykład:**
```php
$data = $table->getData();
```

### `setModel(?ModelInterface $model): void`

Ustawia model do automatycznego pobierania danych.

**Parametry:**
- `$model` (ModelInterface|null) - Instancja modelu

**Przykład:**
```php
$userModel = $this->loadModel('User');
$table->setModel($userModel);
```

### `getModel(): ?ModelInterface`

Pobiera aktualnie ustawiony model.

**Zwraca:** ModelInterface|null

## Metody zarządzania kolumnami

### `addColumn(ColumnInterface $column): void`

Dodaje kolumnę do tabeli.

**Parametry:**
- `$column` (ColumnInterface) - Instancja kolumny

**Przykład:**
```php
$table->addColumn(
    Column::create('name', 'Imię')
        ->setSearch(true)
        ->setSortable(true)
);
```

### `getColumns(): array`

Pobiera wszystkie kolumny tabeli.

**Zwraca:** array - Tablica kolumn

### `prepareColumnValue(ColumnInterface $column, array $data): string`

Przygotowuje wartość kolumny na podstawie danych.

**Parametry:**
- `$column` (ColumnInterface) - Kolumna
- `$data` (array) - Dane wiersza

**Zwraca:** string - Przygotowana wartość

## Metody konfiguracji

### `setId(?string $id, bool $config = true): void`

Ustawia identyfikator tabeli.

**Parametry:**
- `$id` (string|null) - ID tabeli
- `$config` (bool) - Czy wczytać konfigurację

**Przykład:**
```php
$table->setId('my-table');
```

### `getId(): ?string`

Pobiera identyfikator tabeli.

**Zwraca:** string|null

### `setLimit(int $limit): void`

Ustawia liczbę rekordów na stronę.

**Parametry:**
- `$limit` (int) - Limit rekordów

**Przykład:**
```php
$table->setLimit(25);
```

### `getLimit(): int`

Pobiera aktualny limit rekordów.

**Zwraca:** int

### `setPage(int $page): void`

Ustawia aktualną stronę.

**Parametry:**
- `$page` (int) - Numer strony

**Przykład:**
```php
$table->setPage(2);
```

### `getPage(): int`

Pobiera aktualną stronę.

**Zwraca:** int

### `setPageCount(int $pageCount): void`

Ustawia liczbę stron.

**Parametry:**
- `$pageCount` (int) - Liczba stron

### `getPageCount(): int`

Pobiera liczbę stron.

**Zwraca:** int

## Metody wyszukiwania i filtrowania

### `setSearch(string $search): self`

Ustawia tekst wyszukiwania.

**Parametry:**
- `$search` (string) - Tekst do wyszukania

**Zwraca:** self

**Przykład:**
```php
$table->setSearch('kowalski');
```

### `getSearch(): string`

Pobiera aktualny tekst wyszukiwania.

**Zwraca:** string

### `addFilter(FilterInterface $filter): self`

Dodaje filtr do tabeli.

**Parametry:**
- `$filter` (FilterInterface) - Instancja filtru

**Zwraca:** self

**Przykład:**
```php
$statusFilter = Filter::create('status', 'select')
    ->setTitle('Status')
    ->setContent(['active' => 'Aktywny', 'inactive' => 'Nieaktywny'])
    ->setCondition(['users.status' => '%VALUE%']);

$table->addFilter($statusFilter);
```

### `getFilters(): array`

Pobiera wszystkie filtry tabeli.

**Zwraca:** array

## Metody warunków i sortowania

### `setConditions(array $conditions): void`

Ustawia warunki zapytania do bazy danych.

**Parametry:**
- `$conditions` (array) - Tablica warunków

**Przykład:**
```php
$table->setConditions([
    'users.active' => 1,
    'users.role' => 'admin'
]);
```

### `getConditions(): array`

Pobiera wszystkie warunki zapytania.

**Zwraca:** array

### `setOrderBy(?string $orderBy): self`

Ustawia sortowanie.

**Parametry:**
- `$orderBy` (string|null) - Sortowanie (np. 'name ASC')

**Zwraca:** self

**Przykład:**
```php
$table->setOrderBy('users.created_at DESC');
```

### `getOrderBy(): ?string`

Pobiera aktualne sortowanie.

**Zwraca:** string|null

### `setGroupBy(?string $groupBy): void`

Ustawia grupowanie.

**Parametry:**
- `$groupBy` (string|null) - Pole grupowania

**Przykład:**
```php
$table->setGroupBy('users.department');
```

### `getGroupBy(): ?string`

Pobiera aktualne grupowanie.

**Zwraca:** string|null

### `setSortable(bool $sortable): TableInterface`

Włącza/wyłącza sortowanie kolumn.

**Parametry:**
- `$sortable` (bool) - Czy włączyć sortowanie

**Zwraca:** TableInterface

**Przykład:**
```php
$table->setSortable(false); // Wyłącz sortowanie
```

### `isSortable(): bool`

Sprawdza, czy sortowanie jest włączone.

**Zwraca:** bool

### `setSortColumn(array $sortColumn): TableInterface`

Ustawia aktualnie sortowaną kolumnę.

**Parametry:**
- `$sortColumn` (array) - Dane sortowania ['key' => 'kolumna', 'direction' => 'asc|desc']

**Zwraca:** TableInterface

### `getSortColumn(): array`

Pobiera dane aktualnie sortowanej kolumny.

**Zwraca:** array

## Metody AJAX

### `setAjax(bool $ajax = true): self`

Włącza/wyłącza tryb AJAX.

**Parametry:**
- `$ajax` (bool) - Czy włączyć AJAX

**Zwraca:** self

**Przykład:**
```php
$table->setAjax(true);
```

### `isAjax(): bool`

Sprawdza, czy tryb AJAX jest włączony.

**Zwraca:** bool

### `getAjaxKey(): null|int|string`

Pobiera klucz AJAX.

**Zwraca:** null|int|string

### `setAjaxAction(bool $ajaxAction = true): self`

Włącza akcje AJAX (checkboxy do zaznaczania wierszy).

**Parametry:**
- `$ajaxAction` (bool) - Czy włączyć akcje AJAX

**Zwraca:** self

**Przykład:**
```php
$table->setAjaxAction(true);
```

### `hasAjaxAction(): bool`

Sprawdza, czy akcje AJAX są włączone.

**Zwraca:** bool

### `setAjaxActionKey(?string $key): self`

Ustawia klucz dla akcji AJAX.

**Parametry:**
- `$key` (string|null) - Klucz (np. 'users.id')

**Zwraca:** self

### `getAjaxActionKey(): array`

Pobiera klucz akcji AJAX jako tablicę [tabela, kolumna].

**Zwraca:** array

## Metody akcji i stylów

### `addAction(string $name, string $url, string $class = '', bool $ajaxAction = false): self`

Dodaje akcję do tabeli.

**Parametry:**
- `$name` (string) - Nazwa akcji
- `$url` (string) - URL akcji
- `$class` (string) - Klasy CSS
- `$ajaxAction` (bool) - Czy to akcja AJAX

**Zwraca:** self

**Przykład:**
```php
$table->addAction('Dodaj użytkownika', '/users/create', 'btn btn-primary');
```

### `getActions(): array`

Pobiera wszystkie akcje tabeli.

**Zwraca:** array

### `setClass(string $class = ''): void`

Ustawia dodatkowe klasy CSS dla tabeli.

**Parametry:**
- `$class` (string) - Klasy CSS

**Przykład:**
```php
$table->setClass('table-striped table-hover');
```

### `getClass(): string`

Pobiera dodatkowe klasy CSS tabeli.

**Zwraca:** string

### `setLayout(string $layout): void`

Ustawia szablon dla konkretnej tabeli.

**Parametry:**
- `$layout` (string) - Nazwa szablonu ('normal', 'professional', 'modern')

**Przykład:**
```php
$table->setLayout('modern');
```

## Metody konfiguracji

### `saveConfig(): void`

Zapisuje konfigurację tabeli w cookies.

### `readConfig(): void`

Wczytuje konfigurację tabeli z cookies.

### `queryConfig(): void`

Wczytuje konfigurację z parametrów URL.

### `getAjaxConfig(): void`

Pobiera konfigurację AJAX z bazy danych.

### `saveAjaxConfig(): void`

Zapisuje konfigurację AJAX do bazy danych.

## Metoda renderowania

### `render(): string`

Renderuje tabelę do HTML.

**Zwraca:** string - HTML tabeli

**Wyjątki:**
- `DatabaseException` - Błąd bazy danych
- `DatabaseManagerException` - Błąd menedżera bazy danych
- `NimbleException` - Błąd NimblePHP

**Przykład:**
```php
echo $table->render();
```

## Przykład kompleksowego użycia

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;
use NimblePHP\Table\Filter;

// Ustaw język
Table::changeLanguage('pl');

// Utwórz tabelę
$table = new Table('users-management');

// Załaduj model
$userModel = $this->loadModel('User');
$table->setModel($userModel);

// Konfiguracja podstawowa
$table->setAjax(true);
$table->setLimit(20);
$table->setLayout('modern');
$table->setSortable(true);

// Dodaj kolumny
$table->addColumn(
    Column::create('users.id', 'ID')
        ->setStyle(['width' => '60px'])
        ->setSortable(true)
);

$table->addColumn(
    Column::create('users.name', 'Imię i nazwisko')
        ->setSearch(true)
        ->setSortable(true)
);

$table->addColumn(
    Column::create('users.email', 'E-mail')
        ->setSearch(true)
        ->setSortable(true)
);

$table->addColumn(
    Column::create('users.status', 'Status')
        ->setValue(function($cell) {
            $status = $cell->value;
            $class = $status === 'active' ? 'success' : 'danger';
            $text = $status === 'active' ? 'Aktywny' : 'Nieaktywny';
            return "<span class='badge bg-{$class}'>{$text}</span>";
        })
        ->setSearch(false)
        ->setSortable(true)
);

$table->addColumn(
    Column::create('actions', 'Akcje')
        ->setValue(function($cell) {
            $id = $cell->data['users']['id'];
            return "
                <a href='/users/edit/{$id}' class='btn btn-sm btn-primary'>Edytuj</a>
                <a href='/users/delete/{$id}' class='btn btn-sm btn-danger'>Usuń</a>
            ";
        })
        ->setSearch(false)
        ->setSortable(false)
        ->setStyle(['width' => '150px'])
);

// Dodaj filtry
$statusFilter = Filter::create('status', 'select')
    ->setTitle('Status')
    ->setContent([
        '%ALL%' => 'Wszystkie',
        'active' => 'Aktywni',
        'inactive' => 'Nieaktywni'
    ])
    ->setCondition(['users.status' => '%VALUE%']);

$table->addFilter($statusFilter);

$dateFilter = Filter::create('created_date', 'date')
    ->setTitle('Data rejestracji')
    ->setCondition(['users.created_at' => new Condition('users.created_at', '>=', '%VALUE%')]);

$table->addFilter($dateFilter);

// Dodaj akcje
$table->addAction('Dodaj użytkownika', '/users/create', 'btn btn-success');
$table->addAction('Export CSV', '/users/export', 'btn btn-info');

// Włącz akcje AJAX
$table->setAjaxAction(true);
$table->setAjaxActionKey('users.id');

// Ustaw warunki
$table->setConditions([
    'users.deleted_at' => null
]);

// Ustaw klucz AJAX
Table::setAjaxKey(session_id());

// Wyrenderuj tabelę
echo $table->render();
```

## Zobacz także

- [Column](column.md) - Dokumentacja klasy Column
- [Filter](filter.md) - Dokumentacja klasy Filter
- [TableInterface](../interfejsy/table-interface.md) - Interfejs TableInterface
- [Przykłady użycia](../przykłady/) - Więcej przykładów