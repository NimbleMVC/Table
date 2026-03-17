# Klasa Column

Klasa `Column` reprezentuje kolumnę tabeli i implementuje interfejs `ColumnInterface`. Umożliwia definiowanie właściwości kolumn, takich jak nazwa, klucz, możliwość wyszukiwania, sortowania oraz niestandardowe wartości.

## Namespace

```php
NimblePHP\Table\Column
```

## Właściwości

### `$name`
```php
protected string $name
```
Nazwa kolumny wyświetlana w nagłówku tabeli.

### `$key`
```php
protected string $key
```
Klucz kolumny używany do pobierania danych z tablicy/bazy danych.

### `$search`
```php
protected bool $search = true
```
Określa, czy kolumna powinna być uwzględniana w wyszukiwaniu.

### `$value`
```php
protected mixed $value = null
```
Niestandardowa wartość lub funkcja do generowania zawartości kolumny.

### `$style`
```php
protected array $style = []
```
Tablica stylów CSS dla kolumny.

### `$sortable`
```php
protected bool $sortable = true
```
Określa, czy kolumna może być sortowana.

## Metody statyczne

### `create(string $key, ?string $name = null): ColumnInterface`

Tworzy nową instancję kolumny (factory method).

**Parametry:**
- `$key` (string) - Klucz kolumny
- `$name` (string|null) - Nazwa kolumny (opcjonalna)

**Zwraca:** ColumnInterface

**Przykład:**
```php
// Podstawowe utworzenie kolumny
$column = Column::create('id', 'ID');

// Utworzenie kolumny tylko z kluczem
$column = Column::create('user_name');
```

## Metody instancji

### `setName(string $name): ColumnInterface`

Ustawia nazwę kolumny.

**Parametry:**
- `$name` (string) - Nazwa kolumny

**Zwraca:** ColumnInterface

**Przykład:**
```php
$column->setName('Imię i nazwisko');
```

### `getName(): string`

Pobiera nazwę kolumny.

**Zwraca:** string

**Przykład:**
```php
$name = $column->getName();
```

### `setKey(string $key): ColumnInterface`

Ustawia klucz kolumny.

**Parametry:**
- `$key` (string) - Klucz kolumny

**Zwraca:** ColumnInterface

**Przykład:**
```php
$column->setKey('users.full_name');
```

### `getKey(): string`

Pobiera klucz kolumny.

**Zwraca:** string

**Przykład:**
```php
$key = $column->getKey();
```

### `setValue(mixed $value): ColumnInterface`

Ustawia niestandardową wartość lub funkcję dla kolumny.

**Parametry:**
- `$value` (mixed) - Wartość lub funkcja callback

**Zwraca:** ColumnInterface

**Przykłady:**

**Statyczna wartość:**
```php
$column->setValue('Stała wartość');
```

**Funkcja callback:**
```php
$column->setValue(function($cell) {
    return strtoupper($cell->value);
});
```

**Funkcja z HTML:**
```php
$column->setValue(function($cell) {
    $status = $cell->value;
    $class = $status === 'active' ? 'success' : 'danger';
    return "<span class='badge bg-{$class}'>{$status}</span>";
});
```

**Funkcja używająca danych wiersza:**
```php
$column->setValue(function($cell) {
    $id = $cell->data['id'];
    $name = $cell->data['name'];
    return "<a href='/user/{$id}'>{$name}</a>";
});
```

### `getValue(): mixed`

Pobiera wartość kolumny.

**Zwraca:** mixed

**Przykład:**
```php
$value = $column->getValue();
```

### `setSearch(bool $search): ColumnInterface`

Włącza/wyłącza wyszukiwanie w kolumnie.

**Parametry:**
- `$search` (bool) - Czy włączyć wyszukiwanie

**Zwraca:** ColumnInterface

**Przykład:**
```php
// Wyłącz wyszukiwanie dla kolumny akcji
$column->setSearch(false);

// Włącz wyszukiwanie
$column->setSearch(true);
```

### `getSearch(): mixed`

Sprawdza, czy wyszukiwanie jest włączone dla kolumny.

**Zwraca:** mixed (bool)

**Przykład:**
```php
$isSearchable = $column->getSearch();
```

### `setStyle(array $styles = []): ColumnInterface`

Ustawia style CSS dla kolumny.

**Parametry:**
- `$styles` (array) - Tablica stylów CSS

**Zwraca:** ColumnInterface

**Przykład:**
```php
$column->setStyle([
    'width' => '100px',
    'text-align' => 'center',
    'font-weight' => 'bold',
    'color' => '#007bff'
]);
```

### `getStyle(): array`

Pobiera style CSS kolumny.

**Zwraca:** array

**Przykład:**
```php
$styles = $column->getStyle();
```

### `setSortable(bool $sortable): ColumnInterface`

Włącza/wyłącza sortowanie kolumny.

**Parametry:**
- `$sortable` (bool) - Czy włączyć sortowanie

**Zwraca:** ColumnInterface

**Przykład:**
```php
// Wyłącz sortowanie dla kolumny akcji
$column->setSortable(false);

// Włącz sortowanie
$column->setSortable(true);
```

### `isSortable(): bool`

Sprawdza, czy sortowanie jest włączone dla kolumny.

**Zwraca:** bool

**Przykład:**
```php
$isSortable = $column->isSortable();
```

### `getStyleAsString(): string`

Konwertuje style CSS do formatu string.

**Zwraca:** string - Style w formacie CSS

**Przykład:**
```php
$column->setStyle(['width' => '100px', 'color' => 'red']);
$styleString = $column->getStyleAsString();
// Zwróci: "width: 100px; color: red;"
```

## Obiekty Cell w funkcjach callback

Gdy używasz funkcji callback w `setValue()`, otrzymujesz obiekt `Cell` z następującymi właściwościami:

### `$cell->value`
Wartość z bazy danych/tablicy dla danego klucza kolumny.

### `$cell->data`
Pełne dane wiersza jako tablica.

**Przykład struktury danych:**
```php
// Dla modelu
$cell->data = [
    'users' => [
        'id' => 1,
        'name' => 'Jan Kowalski',
        'email' => 'jan@example.com'
    ]
];

// Dla zwykłej tablicy
$cell->data = [
    'id' => 1,
    'name' => 'Jan Kowalski',
    'email' => 'jan@example.com'
];
```

## Przykłady użycia

### Podstawowa kolumna

```php
$column = Column::create('name', 'Imię')
    ->setSearch(true)
    ->setSortable(true);
```

### Kolumna z niestandardowym stylem

```php
$column = Column::create('price', 'Cena')
    ->setStyle([
        'text-align' => 'right',
        'font-weight' => 'bold',
        'color' => '#28a745',
        'width' => '120px'
    ])
    ->setValue(function($cell) {
        return number_format($cell->value, 2, ',', ' ') . ' zł';
    });
```

### Kolumna statusu z kolorami

```php
$column = Column::create('status', 'Status')
    ->setValue(function($cell) {
        $status = $cell->value;
        
        switch($status) {
            case 'active':
                return "<span class='badge bg-success'>Aktywny</span>";
            case 'inactive':
                return "<span class='badge bg-danger'>Nieaktywny</span>";
            case 'pending':
                return "<span class='badge bg-warning'>Oczekujący</span>";
            default:
                return "<span class='badge bg-secondary'>Nieznany</span>";
        }
    })
    ->setSearch(false)
    ->setSortable(true);
```

### Kolumna akcji

```php
$column = Column::create('actions', 'Akcje')
    ->setValue(function($cell) {
        $id = $cell->data['users']['id'] ?? $cell->data['id'];
        
        return "
            <div class='btn-group' role='group'>
                <a href='/users/view/{$id}' class='btn btn-sm btn-info' title='Zobacz'>
                    <i class='fas fa-eye'></i>
                </a>
                <a href='/users/edit/{$id}' class='btn btn-sm btn-primary' title='Edytuj'>
                    <i class='fas fa-edit'></i>
                </a>
                <a href='/users/delete/{$id}' class='btn btn-sm btn-danger' 
                   title='Usuń' onclick='return confirm(\"Czy na pewno?\")'>
                    <i class='fas fa-trash'></i>
                </a>
            </div>
        ";
    })
    ->setSearch(false)
    ->setSortable(false)
    ->setStyle(['width' => '150px', 'text-align' => 'center']);
```

### Kolumna z datą

```php
$column = Column::create('created_at', 'Data utworzenia')
    ->setValue(function($cell) {
        $date = new DateTime($cell->value);
        return $date->format('d.m.Y H:i');
    })
    ->setStyle(['width' => '140px'])
    ->setSortable(true);
```

### Kolumna z linkiem

```php
$column = Column::create('name', 'Nazwa użytkownika')
    ->setValue(function($cell) {
        $id = $cell->data['users']['id'] ?? $cell->data['id'];
        $name = $cell->value;
        
        return "<a href='/users/profile/{$id}' class='text-decoration-none'>{$name}</a>";
    })
    ->setSearch(true)
    ->setSortable(true);
```

### Kolumna z obrazkiem

```php
$column = Column::create('avatar', 'Awatar')
    ->setValue(function($cell) {
        $avatar = $cell->value;
        $name = $cell->data['name'] ?? 'User';
        
        if ($avatar) {
            return "<img src='{$avatar}' alt='{$name}' class='rounded-circle' width='40' height='40'>";
        } else {
            return "<div class='bg-secondary rounded-circle d-flex align-items-center justify-content-center' 
                         style='width: 40px; height: 40px; color: white;'>
                        " . strtoupper(substr($name, 0, 1)) . "
                    </div>";
        }
    })
    ->setSearch(false)
    ->setSortable(false)
    ->setStyle(['width' => '60px', 'text-align' => 'center']);
```

### Kolumna z progresem

```php
$column = Column::create('progress', 'Postęp')
    ->setValue(function($cell) {
        $progress = (int)$cell->value;
        $class = $progress >= 80 ? 'bg-success' : ($progress >= 50 ? 'bg-warning' : 'bg-danger');
        
        return "
            <div class='progress' style='height: 20px;'>
                <div class='progress-bar {$class}' role='progressbar' 
                     style='width: {$progress}%' aria-valuenow='{$progress}' 
                     aria-valuemin='0' aria-valuemax='100'>
                    {$progress}%
                </div>
            </div>
        ";
    })
    ->setSearch(false)
    ->setSortable(true)
    ->setStyle(['width' => '150px']);
```

### Kolumna z warunkowymi stylami

```php
$column = Column::create('balance', 'Saldo')
    ->setValue(function($cell) {
        $balance = (float)$cell->value;
        $color = $balance >= 0 ? 'text-success' : 'text-danger';
        $formatted = number_format($balance, 2, ',', ' ');
        
        return "<span class='{$color} fw-bold'>{$formatted} zł</span>";
    })
    ->setStyle(['text-align' => 'right', 'width' => '120px'])
    ->setSortable(true);
```

## Łączenie metod (method chaining)

Wszystkie metody zwracające `ColumnInterface` można łączyć:

```php
$column = Column::create('users.email', 'E-mail')
    ->setSearch(true)
    ->setSortable(true)
    ->setStyle(['width' => '200px'])
    ->setValue(function($cell) {
        $email = $cell->value;
        return "<a href='mailto:{$email}'>{$email}</a>";
    });
```

## Wskazówki

1. **Klucze dla modeli** - używaj formatu `tabela.kolumna` (np. `users.name`)
2. **Klucze dla tablic** - używaj nazwy klucza z tablicy (np. `name`)
3. **Funkcje callback** - zawsze sprawdzaj istnienie danych przed ich użyciem
4. **Style CSS** - używaj odpowiednich jednostek (px, %, em)
5. **HTML w wartościach** - pamiętaj o bezpieczeństwie i escapowaniu danych użytkownika
6. **Wydajność** - unikaj złożonych operacji w funkcjach callback dla dużych tabel

## Zobacz także

- [Table](table.md) - Dokumentacja klasy Table
- [Cell](cell.md) - Dokumentacja klasy Cell
- [ColumnInterface](../interfejsy/column-interface.md) - Interfejs ColumnInterface
- [Przykłady niestandardowych kolumn](../przykłady/niestandardowe-kolumny.md)