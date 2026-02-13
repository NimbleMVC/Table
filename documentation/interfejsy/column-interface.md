# ColumnInterface

Interfejs `ColumnInterface` definiuje kontrakt dla klas kolumn w bibliotece NimblePHP Table. Wszystkie implementacje kolumn muszą implementować ten interfejs.

## Namespace

```php
NimblePHP\Table\Interfaces\ColumnInterface
```

## Implementowane przez

- [`Column`](../klasy/column.md) - Standardowa klasa kolumny

## Metody

### Factory method

#### `static create(string $key): ColumnInterface`

Tworzy nową instancję kolumny (factory method).

**Parametry:**
- `$key` (string) - Klucz kolumny

**Zwraca:** ColumnInterface - Nowa instancja kolumny

### Zarządzanie nazwą

#### `setName(string $name): ColumnInterface`

Ustawia nazwę kolumny wyświetlaną w nagłówku tabeli.

**Parametry:**
- `$name` (string) - Nazwa kolumny

**Zwraca:** ColumnInterface - Dla method chaining

#### `getName(): string`

Pobiera nazwę kolumny.

**Zwraca:** string - Nazwa kolumny

### Zarządzanie kluczem

#### `setKey(string $key): ColumnInterface`

Ustawia klucz kolumny używany do pobierania danych.

**Parametry:**
- `$key` (string) - Klucz kolumny

**Zwraca:** ColumnInterface - Dla method chaining

#### `getKey(): string`

Pobiera klucz kolumny.

**Zwraca:** string - Klucz kolumny

### Zarządzanie wartością

#### `setValue(mixed $value): ColumnInterface`

Ustawia niestandardową wartość lub funkcję callback dla kolumny.

**Parametry:**
- `$value` (mixed) - Wartość statyczna lub funkcja callback

**Zwraca:** ColumnInterface - Dla method chaining

#### `getValue(): mixed`

Pobiera wartość kolumny.

**Zwraca:** mixed - Wartość lub funkcja callback

### Wyszukiwanie

#### `setSearch(bool $search): ColumnInterface`

Włącza/wyłącza wyszukiwanie w kolumnie.

**Parametry:**
- `$search` (bool) - Czy włączyć wyszukiwanie

**Zwraca:** ColumnInterface - Dla method chaining

#### `getSearch(): mixed`

Sprawdza, czy wyszukiwanie jest włączone dla kolumny.

**Zwraca:** mixed - Status wyszukiwania (bool)

### Style CSS

#### `setStyle(array $styles): ColumnInterface`

Ustawia style CSS dla kolumny.

**Parametry:**
- `$styles` (array) - Tablica stylów CSS (klucz => wartość)

**Zwraca:** ColumnInterface - Dla method chaining

#### `getStyle(): array`

Pobiera style CSS kolumny.

**Zwraca:** array - Tablica stylów CSS

#### `getStyleAsString(): string`

Konwertuje style CSS do formatu string gotowego do użycia w HTML.

**Zwraca:** string - Style w formacie CSS

## Przykład implementacji

```php
<?php

use NimblePHP\Table\Interfaces\ColumnInterface;

class CustomColumn implements ColumnInterface
{
    protected string $name = '';
    protected string $key = '';
    protected bool $search = true;
    protected mixed $value = null;
    protected array $style = [];
    
    public static function create(string $key): ColumnInterface
    {
        $instance = new static();
        $instance->setKey($key);
        return $instance;
    }
    
    public function setName(string $name): ColumnInterface
    {
        $this->name = $name;
        return $this;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setKey(string $key): ColumnInterface
    {
        $this->key = $key;
        return $this;
    }
    
    public function getKey(): string
    {
        return $this->key;
    }
    
    public function setValue(mixed $value): ColumnInterface
    {
        $this->value = $value;
        return $this;
    }
    
    public function getValue(): mixed
    {
        return $this->value;
    }
    
    public function setSearch(bool $search): ColumnInterface
    {
        $this->search = $search;
        return $this;
    }
    
    public function getSearch(): mixed
    {
        return $this->search;
    }
    
    public function setStyle(array $styles): ColumnInterface
    {
        $this->style = $styles;
        return $this;
    }
    
    public function getStyle(): array
    {
        return $this->style;
    }
    
    public function getStyleAsString(): string
    {
        if (empty($this->style)) {
            return '';
        }
        
        $styleString = '';
        foreach ($this->style as $property => $value) {
            $styleString .= "{$property}: {$value}; ";
        }
        
        return trim($styleString);
    }
}
```

## Przykłady użycia

### Podstawowe użycie

```php
<?php

use NimblePHP\Table\Column;

// Standardowa implementacja
$column = Column::create('user_name', 'Nazwa użytkownika')
    ->setSearch(true)
    ->setStyle(['width' => '200px'])
    ->setValue(function($cell) {
        return strtoupper($cell->value);
    });

// Sprawdzenie implementacji interfejsu
if ($column instanceof ColumnInterface) {
    echo "Klucz: " . $column->getKey() . "\n";
    echo "Nazwa: " . $column->getName() . "\n";
    echo "Wyszukiwanie: " . ($column->getSearch() ? 'tak' : 'nie') . "\n";
    echo "Style: " . $column->getStyleAsString() . "\n";
}
```

### Niestandardowa implementacja

```php
<?php

class IconColumn implements ColumnInterface
{
    protected string $key = '';
    protected string $name = '';
    protected array $iconMap = [];
    
    public static function create(string $key): ColumnInterface
    {
        return (new static())->setKey($key);
    }
    
    public function setIconMap(array $iconMap): self
    {
        $this->iconMap = $iconMap;
        return $this;
    }
    
    public function setValue(mixed $value): ColumnInterface
    {
        // Ignoruj - używamy własnej logiki
        return $this;
    }
    
    public function getValue(): mixed
    {
        return function($cell) {
            $value = $cell->value;
            $icon = $this->iconMap[$value] ?? '❓';
            return "<i>{$icon}</i> {$value}";
        };
    }
    
    // ... pozostałe implementacje metod interfejsu
}

// Użycie
$statusColumn = IconColumn::create('status')
    ->setName('Status')
    ->setIconMap([
        'active' => '✅',
        'inactive' => '❌', 
        'pending' => '⏳'
    ]);
```

### Kolumna z walidacją

```php
<?php

class ValidatedColumn implements ColumnInterface
{
    protected array $validators = [];
    
    public function addValidator(callable $validator): self
    {
        $this->validators[] = $validator;
        return $this;
    }
    
    public function setValue(mixed $value): ColumnInterface
    {
        if (is_callable($value)) {
            $originalCallback = $value;
            
            $this->value = function($cell) use ($originalCallback) {
                $result = $originalCallback($cell);
                
                // Waliduj wynik
                foreach ($this->validators as $validator) {
                    if (!$validator($result)) {
                        return "<span class='text-danger'>Błędne dane</span>";
                    }
                }
                
                return $result;
            };
        } else {
            $this->value = $value;
        }
        
        return $this;
    }
    
    // ... pozostałe implementacje
}

// Użycie
$emailColumn = ValidatedColumn::create('email')
    ->setName('E-mail')
    ->addValidator(function($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    })
    ->setValue(function($cell) {
        return $cell->value;
    });
```

## Method Chaining

Interfejs wspiera method chaining dla większości metod:

```php
$column = Column::create('product_price', 'Cena produktu')
    ->setSearch(false)
    ->setStyle([
        'text-align' => 'right',
        'font-weight' => 'bold',
        'color' => '#28a745'
    ])
    ->setValue(function($cell) {
        $price = number_format($cell->value, 2, ',', ' ');
        return "{$price} zł";
    });
```

## Wskazówki implementacji

1. **Factory method** - zawsze implementuj metodę `create()` jako statyczną
2. **Method chaining** - zwracaj `$this` lub `ColumnInterface` z metod setter
3. **Walidacja** - sprawdzaj poprawność przekazywanych parametrów
4. **Domyślne wartości** - ustaw sensowne wartości domyślne
5. **Dokumentacja** - dokumentuj niestandardowe zachowania
6. **Kompatybilność** - zachowuj zgodność z interfejsem

## Testowanie implementacji

```php
<?php

function testColumnInterface(ColumnInterface $column): void
{
    // Test factory method
    $newColumn = $column::create('test_key');
    assert($newColumn instanceof ColumnInterface);
    assert($newColumn->getKey() === 'test_key');
    
    // Test method chaining
    $result = $column->setName('Test')
        ->setSearch(false)
        ->setStyle(['width' => '100px']);
    assert($result instanceof ColumnInterface);
    
    // Test getters
    assert(is_string($column->getName()));
    assert(is_string($column->getKey()));
    assert(is_array($column->getStyle()));
    assert(is_string($column->getStyleAsString()));
    
    echo "Wszystkie testy przeszły pomyślnie!\n";
}

// Test standardowej implementacji
testColumnInterface(new Column());
```

## Zobacz także

- [Column](../klasy/column.md) - Standardowa implementacja ColumnInterface
- [Cell](../klasy/cell.md) - Klasa Cell używana w funkcjach callback
- [TableInterface](table-interface.md) - Interfejs tabel
- [Przykłady niestandardowych kolumn](../przykłady/niestandardowe-kolumny.md)