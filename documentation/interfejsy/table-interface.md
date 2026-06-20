# TableInterface

Interfejs `TableInterface` definiuje kontrakt dla klas tabel w bibliotece NimblePHP Table. Wszystkie implementacje tabel muszą implementować ten interfejs.

## Namespace

```php
NimblePHP\Table\Interfaces\TableInterface
```

## Implementowane przez

- [`Table`](../klasy/table.md) - Główna klasa tabeli

## Metody

### Zarządzanie danymi

#### `setData(array $data): void`

Ustawia dane tabeli jako tablicę.

**Parametry:**
- `$data` (array) - Tablica danych do wyświetlenia

#### `getData(): array`

Pobiera dane tabeli. Jeśli ustawiony jest model, dane są pobierane automatycznie z bazy danych.

**Zwraca:** array - Tablica danych

### Renderowanie

#### `render(): string`

Renderuje tabelę do HTML.

**Zwraca:** string - HTML tabeli

**Wyjątki:**
- `DatabaseException` - Błąd bazy danych
- `DatabaseManagerException` - Błąd menedżera bazy danych  
- `NimbleException` - Błąd NimblePHP

### Zarządzanie kolumnami

#### `addColumn(ColumnInterface $column): void`

Dodaje kolumnę do tabeli.

**Parametry:**
- `$column` (ColumnInterface) - Instancja kolumny

#### `getColumns(): array`

Pobiera wszystkie kolumny tabeli.

**Zwraca:** array - Tablica kolumn

#### `prepareColumnValue(ColumnInterface $column, array $data): string`

Przygotowuje wartość kolumny na podstawie danych.

**Parametry:**
- `$column` (ColumnInterface) - Kolumna
- `$data` (array) - Dane wiersza

**Zwraca:** string - Przygotowana wartość

### Konfiguracja tabeli

#### `getId(): ?string`

Pobiera identyfikator tabeli.

**Zwraca:** string|null - ID tabeli

#### `setId(?string $id): void`

Ustawia identyfikator tabeli.

**Parametry:**
- `$id` (string|null) - ID tabeli

### Zarządzanie modelem

#### `getModel(): ?ModelInterface`

Pobiera aktualnie ustawiony model.

**Zwraca:** ModelInterface|null - Model lub null

#### `setModel(?ModelInterface $model): void`

Ustawia model do automatycznego pobierania danych.

**Parametry:**
- `$model` (ModelInterface|null) - Instancja modelu

### Warunki i filtrowanie

#### `getConditions(): array`

Pobiera wszystkie warunki zapytania.

**Zwraca:** array - Tablica warunków

#### `setConditions(array $conditions): void`

Ustawia warunki zapytania do bazy danych.

**Parametry:**
- `$conditions` (array) - Tablica warunków

### Paginacja

#### `getLimit(): int`

Pobiera aktualny limit rekordów na stronę.

**Zwraca:** int - Limit rekordów

#### `setLimit(int $limit): void`

Ustawia liczbę rekordów na stronę.

**Parametry:**
- `$limit` (int) - Limit rekordów

#### `getPage(): int`

Pobiera aktualną stronę.

**Zwraca:** int - Numer strony

#### `setPage(int $page): void`

Ustawia aktualną stronę.

**Parametry:**
- `$page` (int) - Numer strony

#### `getPageCount(): int`

Pobiera liczbę stron.

**Zwraca:** int - Liczba stron

#### `setPageCount(int $pageCount): void`

Ustawia liczbę stron.

**Parametry:**
- `$pageCount` (int) - Liczba stron

### Konfiguracja

#### `readConfig(): void`

Wczytuje konfigurację tabeli z cookies.

#### `saveConfig(): void`

Zapisuje konfigurację tabeli w cookies.

#### `queryConfig(): void`

Wczytuje konfigurację z parametrów URL.

### Style i klasy CSS

#### `setClass(string $class = ''): void`

Ustawia dodatkowe klasy CSS dla tabeli.

**Parametry:**
- `$class` (string) - Klasy CSS

#### `getClass(): string`

Pobiera dodatkowe klasy CSS tabeli.

**Zwraca:** string - Klasy CSS

### AJAX

#### `setAjaxKey(null|int|string $ajaxKey): void`

Ustawia klucz AJAX używany do rozróżniania konfiguracji użytkowników.

**Parametry:**
- `$ajaxKey` (null|int|string) - Klucz AJAX

#### `getAjaxKey(): null|int|string`

Pobiera klucz AJAX.

**Zwraca:** null|int|string - Klucz AJAX

#### `setAjax(bool $ajax = true): self`

Włącza/wyłącza tryb AJAX.

**Parametry:**
- `$ajax` (bool) - Czy włączyć AJAX

**Zwraca:** self

#### `isAjax(): bool`

Sprawdza, czy tryb AJAX jest włączony.

**Zwraca:** bool - Czy AJAX jest włączony

#### `getAjaxConfig(): void`

Pobiera konfigurację AJAX z bazy danych.

**Wyjątki:**
- `DatabaseManagerException` - Błąd menedżera bazy danych

#### `saveAjaxConfig(): void`

Zapisuje konfigurację AJAX do bazy danych.

**Wyjątki:**
- `DatabaseManagerException` - Błąd menedżera bazy danych

### Akcje

#### `getActions(): array`

Pobiera wszystkie akcje tabeli.

**Zwraca:** array - Tablica akcji

## Przykład implementacji

```php
<?php

use NimblePHP\Table\Interfaces\TableInterface;
use NimblePHP\Table\Interfaces\ColumnInterface;
use NimblePHP\Framework\Interfaces\ModelInterface;

class CustomTable implements TableInterface
{
    protected array $data = [];
    protected array $columns = [];
    protected ?string $id = null;
    protected ?ModelInterface $model = null;
    protected array $conditions = [];
    protected int $limit = 10;
    protected int $page = 1;
    protected int $pageCount = 1;
    protected string $class = '';
    protected array $actions = [];
    protected bool $ajax = false;
    protected static $ajaxKey = null;
    
    // Implementacja wszystkich metod interfejsu...
    
    public function setData(array $data): void
    {
        $this->data = $data;
    }
    
    public function getData(): array
    {
        if ($this->model && empty($this->data)) {
            // Pobierz dane z modelu
            return $this->model->readAll(
                $this->getConditions(),
                null,
                null,
                (($this->getPage() - 1) * $this->getLimit()) . ',' . $this->getLimit()
            );
        }
        
        return $this->data;
    }
    
    public function render(): string
    {
        // Implementacja renderowania tabeli
        $html = '<table class="table ' . $this->getClass() . '">';
        
        // Nagłówek
        $html .= '<thead><tr>';
        foreach ($this->columns as $column) {
            $html .= '<th>' . $column->getName() . '</th>';
        }
        $html .= '</tr></thead>';
        
        // Dane
        $html .= '<tbody>';
        foreach ($this->getData() as $row) {
            $html .= '<tr>';
            foreach ($this->columns as $column) {
                $value = $this->prepareColumnValue($column, $row);
                $html .= '<td>' . $value . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        
        $html .= '</table>';
        
        return $html;
    }
    
    public function addColumn(ColumnInterface $column): void
    {
        $this->columns[] = $column;
    }
    
    public function getColumns(): array
    {
        return $this->columns;
    }
    
    public function prepareColumnValue(ColumnInterface $column, array $data): string
    {
        if (is_callable($column->getValue())) {
            $cell = new \NimblePHP\Table\Cell();
            $cell->value = $data[$column->getKey()] ?? '';
            $cell->data = $data;
            
            return $column->getValue()($cell);
        }
        
        return $data[$column->getKey()] ?? '';
    }
    
    // ... pozostałe implementacje metod
}
```

## Przykład użycia

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;

// Użycie standardowej implementacji
$table = new Table('my-table');

// Tabela implementuje TableInterface
if ($table instanceof TableInterface) {
    // Można używać wszystkich metod interfejsu
    $table->setData([
        ['id' => 1, 'name' => 'Test']
    ]);
    
    $table->addColumn(
        Column::create('id', 'ID')
    );
    
    $table->addColumn(
        Column::create('name', 'Nazwa')
    );
    
    echo $table->render();
}
```

## Wskazówki implementacji

1. **Walidacja parametrów** - zawsze sprawdzaj poprawność przekazywanych danych
2. **Obsługa błędów** - implementuj odpowiednią obsługę wyjątków
3. **Wydajność** - optymalizuj zapytania do bazy danych
4. **Bezpieczeństwo** - escapuj dane wyjściowe HTML
5. **Kompatybilność** - zachowuj zgodność z interfejsem
6. **Dokumentacja** - dokumentuj niestandardowe zachowania

## Zobacz także

- [Table](../klasy/table.md) - Implementacja TableInterface
- [ColumnInterface](column-interface.md) - Interfejs kolumn
- [FilterInterface](filter-interface.md) - Interfejs filtrów
- [Przykłady użycia](../przykłady/) - Praktyczne przykłady