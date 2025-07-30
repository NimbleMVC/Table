# Klasa Filter

Klasa `Filter` umożliwia tworzenie filtrów do tabel, implementując interfejs `FilterInterface`. Obsługuje różne typy filtrów: select, date, checkbox, które pozwalają użytkownikom na zawężanie wyświetlanych danych.

## Namespace

```php
NimblePHP\Table\Filter
```

## Właściwości

### `$condition`
```php
protected array $condition = []
```
Aktualne warunki filtru stosowane do zapytania.

### `$baseCondition`
```php
protected array $baseCondition = []
```
Podstawowe warunki filtru (szablon).

### `$title`
```php
protected ?string $title = null
```
Tytuł filtru wyświetlany w interfejsie.

### `$type`
```php
protected string $type = 'select'
```
Typ filtru. Dostępne opcje: `'select'`, `'date'`, `'checkbox'`.

### `$key`
```php
protected string $key = ''
```
Unikalny klucz filtru.

### `$content`
```php
protected mixed $content = null
```
Zawartość filtru (opcje dla select, itp.).

### `$value`
```php
protected string $value
```
Aktualna wartość filtru.

## Metody statyczne

### `create(string $key, ?string $type = null): self`

Tworzy nową instancję filtru (factory method).

**Parametry:**
- `$key` (string) - Unikalny klucz filtru
- `$type` (string|null) - Typ filtru (domyślnie 'select')

**Zwraca:** self

**Przykład:**
```php
// Filtr select
$filter = Filter::create('status', 'select');

// Filtr daty
$filter = Filter::create('created_date', 'date');

// Filtr checkbox
$filter = Filter::create('is_active', 'checkbox');
```

## Metody instancji

### `setTitle(?string $title): self`

Ustawia tytuł filtru.

**Parametry:**
- `$title` (string|null) - Tytuł filtru

**Zwraca:** self

**Przykład:**
```php
$filter->setTitle('Status użytkownika');
```

### `getTitle(): ?string`

Pobiera tytuł filtru.

**Zwraca:** string|null

**Przykład:**
```php
$title = $filter->getTitle();
```

### `setType(string $type): self`

Ustawia typ filtru.

**Parametry:**
- `$type` (string) - Typ filtru ('select', 'date', 'checkbox')

**Zwraca:** self

**Przykład:**
```php
$filter->setType('date');
```

### `getType(): ?string`

Pobiera typ filtru.

**Zwraca:** string|null

**Przykład:**
```php
$type = $filter->getType();
```

### `setKey(string $key): self`

Ustawia klucz filtru.

**Parametry:**
- `$key` (string) - Klucz filtru

**Zwraca:** self

**Przykład:**
```php
$filter->setKey('user_status');
```

### `getKey(): string`

Pobiera klucz filtru.

**Zwraca:** string

**Przykład:**
```php
$key = $filter->getKey();
```

### `setContent(mixed $content): self`

Ustawia zawartość filtru (opcje dla select, itp.).

**Parametry:**
- `$content` (mixed) - Zawartość filtru

**Zwraca:** self

**Przykład:**
```php
// Dla filtru select
$filter->setContent([
    '%ALL%' => 'Wszystkie',
    'active' => 'Aktywni',
    'inactive' => 'Nieaktywni',
    'banned' => 'Zablokowani'
]);
```

### `getContent(): mixed`

Pobiera zawartość filtru.

**Zwraca:** mixed

**Przykład:**
```php
$content = $filter->getContent();
```

### `setCondition(array $condition): self`

Ustawia warunki filtru.

**Parametry:**
- `$condition` (array) - Tablica warunków

**Zwraca:** self

**Przykład:**
```php
use krzysztofzylka\DatabaseManager\Condition;

// Prosty warunek
$filter->setCondition(['users.status' => '%VALUE%']);

// Warunek z obiektem Condition
$filter->setCondition([
    'users.created_at' => new Condition('users.created_at', '>=', '%VALUE%')
]);
```

### `getCondition(): array`

Pobiera aktualne warunki filtru.

**Zwraca:** array

**Przykład:**
```php
$conditions = $filter->getCondition();
```

### `setValue(mixed $value): self`

Ustawia wartość filtru i przetwarza warunki.

**Parametry:**
- `$value` (mixed) - Wartość filtru

**Zwraca:** self

**Przykład:**
```php
$filter->setValue('active');
```

### `getValue(): ?string`

Pobiera aktualną wartość filtru.

**Zwraca:** string|null

**Przykład:**
```php
$value = $filter->getValue();
```

### `render(Table $table): string`

Renderuje filtr do HTML.

**Parametry:**
- `$table` (Table) - Instancja tabeli

**Zwraca:** string - HTML filtru

**Przykład:**
```php
$html = $filter->render($table);
```

## Typy filtrów

### Filtr Select

Najczęściej używany typ filtru z rozwijaną listą opcji.

**Przykład:**
```php
$statusFilter = Filter::create('status', 'select')
    ->setTitle('Status')
    ->setContent([
        '%ALL%' => 'Wszystkie',
        'active' => 'Aktywni użytkownicy',
        'inactive' => 'Nieaktywni użytkownicy',
        'pending' => 'Oczekujący na aktywację'
    ])
    ->setCondition(['users.status' => '%VALUE%']);
```

### Filtr Date

Filtr daty z kalendarzem HTML5.

**Przykład:**
```php
use krzysztofzylka\DatabaseManager\Condition;

$dateFilter = Filter::create('registration_date', 'date')
    ->setTitle('Data rejestracji od')
    ->setCondition([
        'users.created_at' => new Condition('users.created_at', '>=', '%VALUE%')
    ]);
```

### Filtr Checkbox

Filtr typu checkbox do filtrowania wartości boolean.

**Przykład:**
```php
$activeFilter = Filter::create('is_active', 'checkbox')
    ->setTitle('Tylko aktywni')
    ->setCondition(['users.active' => 1]);
```

## Zaawansowane przykłady

### Filtr z wieloma warunkami

```php
use krzysztofzylka\DatabaseManager\Condition;

$advancedFilter = Filter::create('advanced_status', 'select')
    ->setTitle('Zaawansowany status')
    ->setContent([
        '%ALL%' => 'Wszystkie',
        'new_active' => 'Nowi i aktywni',
        'old_inactive' => 'Starzy i nieaktywni'
    ])
    ->setCondition([
        'OR' => [
            [
                'users.status' => '%VALUE%',
                'users.created_at' => new Condition('users.created_at', '>', '2023-01-01')
            ],
            [
                'users.status' => 'inactive',
                'users.created_at' => new Condition('users.created_at', '<', '2022-01-01')
            ]
        ]
    ]);
```

### Filtr z dynamiczną zawartością

```php
// W kontrolerze - pobierz opcje z bazy danych
$categoryModel = $this->loadModel('Category');
$categories = $categoryModel->readAll();

$categoryOptions = ['%ALL%' => 'Wszystkie kategorie'];
foreach ($categories as $category) {
    $categoryOptions[$category['categories']['id']] = $category['categories']['name'];
}

$categoryFilter = Filter::create('category_id', 'select')
    ->setTitle('Kategoria')
    ->setContent($categoryOptions)
    ->setCondition(['products.category_id' => '%VALUE%']);
```

### Filtr zakresu dat

```php
use krzysztofzylka\DatabaseManager\Condition;

// Filtr "od" daty
$dateFromFilter = Filter::create('date_from', 'date')
    ->setTitle('Data od')
    ->setCondition([
        'orders.created_at' => new Condition('orders.created_at', '>=', '%VALUE%')
    ]);

// Filtr "do" daty
$dateToFilter = Filter::create('date_to', 'date')
    ->setTitle('Data do')
    ->setCondition([
        'orders.created_at' => new Condition('orders.created_at', '<=', '%VALUE% 23:59:59')
    ]);

$table->addFilter($dateFromFilter);
$table->addFilter($dateToFilter);
```

### Filtr z warunkami LIKE

```php
use krzysztofzylka\DatabaseManager\Condition;

$searchFilter = Filter::create('name_search', 'select')
    ->setTitle('Nazwa zawiera')
    ->setContent([
        '%ALL%' => 'Wszystkie',
        'admin' => 'Zawiera "admin"',
        'user' => 'Zawiera "user"',
        'test' => 'Zawiera "test"'
    ])
    ->setCondition([
        'users.name' => new Condition('users.name', 'LIKE', '%VALUE%')
    ]);
```

### Filtr numeryczny

```php
use krzysztofzylka\DatabaseManager\Condition;

$priceFilter = Filter::create('price_range', 'select')
    ->setTitle('Zakres cen')
    ->setContent([
        '%ALL%' => 'Wszystkie',
        'low' => 'Do 100 zł',
        'medium' => '100-500 zł',
        'high' => 'Powyżej 500 zł'
    ])
    ->setCondition([
        'products.price' => new Condition('products.price', '<=', '%VALUE%')
    ]);

// Dodatkowa logika w setValue
$priceFilter->setValue(function($value) {
    switch($value) {
        case 'low':
            return new Condition('products.price', '<=', 100);
        case 'medium':
            return [
                'products.price' => new Condition('products.price', '>=', 100),
                'products.price' => new Condition('products.price', '<=', 500)
            ];
        case 'high':
            return new Condition('products.price', '>', 500);
        default:
            return [];
    }
});
```

## Specjalne wartości

### `%ALL%`
Specjalna wartość używana w filtrach select do reprezentowania opcji "wszystkie". Gdy użytkownik wybierze tę opcję, filtr nie będzie stosowany.

### `%VALUE%`
Placeholder używany w warunkach, który zostanie zastąpiony rzeczywistą wartością filtru.

**Przykład:**
```php
$filter->setCondition(['users.status' => '%VALUE%']);
// Gdy użytkownik wybierze 'active', warunek stanie się:
// ['users.status' => 'active']
```

## Integracja z tabelą

```php
use NimblePHP\Table\Table;
use NimblePHP\Table\Filter;
use NimblePHP\Table\Column;

$table = new Table('filtered-users');

// Załaduj model
$userModel = $this->loadModel('User');
$table->setModel($userModel);

// Dodaj kolumny
$table->addColumn(Column::create('users.name', 'Imię'));
$table->addColumn(Column::create('users.status', 'Status'));
$table->addColumn(Column::create('users.created_at', 'Data rejestracji'));

// Dodaj filtry
$statusFilter = Filter::create('status', 'select')
    ->setTitle('Status')
    ->setContent([
        '%ALL%' => 'Wszystkie',
        'active' => 'Aktywni',
        'inactive' => 'Nieaktywni'
    ])
    ->setCondition(['users.status' => '%VALUE%']);

$dateFilter = Filter::create('registration_date', 'date')
    ->setTitle('Zarejestrowany po')
    ->setCondition([
        'users.created_at' => new Condition('users.created_at', '>=', '%VALUE%')
    ]);

$activeOnlyFilter = Filter::create('active_only', 'checkbox')
    ->setTitle('Tylko aktywni')
    ->setCondition(['users.active' => 1]);

$table->addFilter($statusFilter);
$table->addFilter($dateFilter);
$table->addFilter($activeOnlyFilter);

echo $table->render();
```

## Stylizacja filtrów

Filtry używają klas Bootstrap do stylizacji:

- **Select**: `form-select form-select-sm ajax-form mb-2`
- **Date**: `form-control form-control-sm ajax-form mb-2`
- **Checkbox**: `form-check-input ajax-checkbox mb-2`

Możesz dostosować style przez CSS:

```css
/* Niestandardowe style dla filtrów */
.ajax-form {
    border-radius: 8px;
    border-color: #dee2e6;
}

.ajax-checkbox {
    transform: scale(1.2);
}

/* Style dla kontenerów filtrów */
.filter-container {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}
```

## Wskazówki

1. **Klucze filtrów** powinny być unikalne w ramach tabeli
2. **Wartość %ALL%** zawsze powinna być pierwszą opcją w filtrach select
3. **Warunki** używaj obiektów `Condition` dla zaawansowanych operacji
4. **Wydajność** - unikaj zbyt wielu filtrów na jednej tabeli
5. **UX** - używaj opisowych tytułów i opcji
6. **Testowanie** - sprawdź wszystkie kombinacje filtrów

## Rozwiązywanie problemów

### Filtr nie działa
- Sprawdź czy klucz filtru jest unikalny
- Upewnij się, że warunki są poprawnie zdefiniowane
- Sprawdź czy kolumna istnieje w bazie danych

### Wartości nie są zachowywane
- Upewnij się, że tryb AJAX jest włączony
- Sprawdź konfigurację bazy danych
- Zweryfikuj klucz AJAX

### Błędy JavaScript
- Sprawdź czy plik `table.js` jest załadowany
- Upewnij się, że Bootstrap JS jest włączony

## Zobacz także

- [Table](table.md) - Dokumentacja klasy Table
- [FilterInterface](../interfejsy/filter-interface.md) - Interfejs FilterInterface
- [Przykłady z filtrami](../przykłady/tabela-z-filtrami.md)
- [Tabela AJAX](../przykłady/tabela-ajax.md)