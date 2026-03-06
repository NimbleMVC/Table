# FilterInterface

Interfejs `FilterInterface` definiuje kontrakt dla klas filtrów w bibliotece NimblePHP Table. Wszystkie implementacje filtrów muszą implementować ten interfejs.

## Namespace

```php
NimblePHP\Table\Interfaces\FilterInterface
```

## Implementowane przez

- [`Filter`](../klasy/filter.md) - Standardowa klasa filtru

## Metody

### Factory method

#### `static create(string $key, ?string $type = null): self`

Tworzy nową instancję filtru (factory method).

**Parametry:**
- `$key` (string) - Unikalny klucz filtru
- `$type` (string|null) - Typ filtru ('select', 'date', 'checkbox')

**Zwraca:** self - Nowa instancja filtru

### Renderowanie

#### `render(Table $table): string`

Renderuje filtr do HTML.

**Parametry:**
- `$table` (Table) - Instancja tabeli

**Zwraca:** string - HTML filtru

### Zarządzanie tytułem

#### `setTitle(?string $title): self`

Ustawia tytuł filtru wyświetlany w interfejsie.

**Parametry:**
- `$title` (string|null) - Tytuł filtru

**Zwraca:** self - Dla method chaining

#### `getTitle(): ?string`

Pobiera tytuł filtru.

**Zwraca:** string|null - Tytuł filtru

### Zarządzanie warunkami

#### `setCondition(array $condition): self`

Ustawia warunki filtru stosowane do zapytania.

**Parametry:**
- `$condition` (array) - Tablica warunków

**Zwraca:** self - Dla method chaining

#### `getCondition(): array`

Pobiera aktualne warunki filtru.

**Zwraca:** array - Tablica warunków

### Zarządzanie typem

#### `setType(string $type): self`

Ustawia typ filtru.

**Parametry:**
- `$type` (string) - Typ filtru ('select', 'date', 'checkbox')

**Zwraca:** self - Dla method chaining

#### `getType(): ?string`

Pobiera typ filtru.

**Zwraca:** string|null - Typ filtru

### Zarządzanie kluczem

#### `setKey(string $key): self`

Ustawia klucz filtru.

**Parametry:**
- `$key` (string) - Klucz filtru

**Zwraca:** self - Dla method chaining

#### `getKey(): string`

Pobiera klucz filtru.

**Zwraca:** string - Klucz filtru

### Zarządzanie zawartością

#### `setContent(mixed $content): self`

Ustawia zawartość filtru (opcje dla select, itp.).

**Parametry:**
- `$content` (mixed) - Zawartość filtru

**Zwraca:** self - Dla method chaining

#### `getContent(): mixed`

Pobiera zawartość filtru.

**Zwraca:** mixed - Zawartość filtru

### Zarządzanie wartością

#### `setValue(string $value): self`

Ustawia wartość filtru i przetwarza warunki.

**Parametry:**
- `$value` (string) - Wartość filtru

**Zwraca:** self - Dla method chaining

#### `getValue(): ?string`

Pobiera aktualną wartość filtru.

**Zwraca:** string|null - Wartość filtru

## Przykład implementacji

```php
<?php

use NimblePHP\Table\Interfaces\FilterInterface;
use NimblePHP\Table\Table;

class CustomFilter implements FilterInterface
{
    protected string $key = '';
    protected ?string $title = null;
    protected string $type = 'select';
    protected mixed $content = null;
    protected array $condition = [];
    protected array $baseCondition = [];
    protected ?string $value = null;
    
    public static function create(string $key, ?string $type = null): self
    {
        $instance = new static();
        $instance->setKey($key);
        
        if ($type !== null) {
            $instance->setType($type);
        }
        
        return $instance;
    }
    
    public function render(Table $table): string
    {
        switch ($this->getType()) {
            case 'select':
                return $this->renderSelect();
            case 'date':
                return $this->renderDate();
            case 'checkbox':
                return $this->renderCheckbox();
            default:
                return '';
        }
    }
    
    protected function renderSelect(): string
    {
        $options = '';
        
        if (is_array($this->getContent())) {
            foreach ($this->getContent() as $value => $label) {
                $selected = (string)$this->getValue() === (string)$value ? 'selected' : '';
                $options .= "<option value='{$value}' {$selected}>{$label}</option>";
            }
        }
        
        return "
            <div class='filter-container'>
                <label>{$this->getTitle()}</label>
                <select name='filter-{$this->getKey()}' class='form-select'>
                    {$options}
                </select>
            </div>
        ";
    }
    
    protected function renderDate(): string
    {
        $value = $this->getValue() ?? '';
        
        return "
            <div class='filter-container'>
                <label>{$this->getTitle()}</label>
                <input type='date' name='filter-{$this->getKey()}' 
                       value='{$value}' class='form-control'>
            </div>
        ";
    }
    
    protected function renderCheckbox(): string
    {
        $checked = (bool)$this->getValue() ? 'checked' : '';
        
        return "
            <div class='filter-container'>
                <div class='form-check'>
                    <input type='checkbox' name='filter-{$this->getKey()}' 
                           {$checked} class='form-check-input' id='filter-{$this->getKey()}'>
                    <label class='form-check-label' for='filter-{$this->getKey()}'>
                        {$this->getTitle()}
                    </label>
                </div>
            </div>
        ";
    }
    
    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }
    
    public function getTitle(): ?string
    {
        return $this->title;
    }
    
    public function setCondition(array $condition): self
    {
        $this->baseCondition = $condition;
        $this->condition = $condition;
        return $this;
    }
    
    public function getCondition(): array
    {
        if ($this->value === null || $this->value === '%ALL%') {
            return [];
        }
        
        return $this->condition;
    }
    
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
    
    public function getType(): ?string
    {
        return $this->type;
    }
    
    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }
    
    public function getKey(): string
    {
        return $this->key;
    }
    
    public function setContent(mixed $content): self
    {
        $this->content = $content;
        return $this;
    }
    
    public function getContent(): mixed
    {
        return $this->content;
    }
    
    public function setValue(string $value): self
    {
        $this->value = $value;
        
        // Przetwórz warunki - zamień %VALUE% na rzeczywistą wartość
        $processedConditions = [];
        foreach ($this->baseCondition as $key => $condition) {
            if (is_string($condition) && str_contains($condition, '%VALUE%')) {
                $processedConditions[$key] = str_replace('%VALUE%', $value, $condition);
            } else {
                $processedConditions[$key] = $condition;
            }
        }
        
        $this->condition = $processedConditions;
        return $this;
    }
    
    public function getValue(): ?string
    {
        return $this->value;
    }
}
```

## Przykłady użycia

### Podstawowe użycie

```php
<?php

use NimblePHP\Table\Filter;

// Standardowa implementacja
$statusFilter = Filter::create('status', 'select')
    ->setTitle('Status użytkownika')
    ->setContent([
        '%ALL%' => 'Wszystkie',
        'active' => 'Aktywni',
        'inactive' => 'Nieaktywni'
    ])
    ->setCondition(['users.status' => '%VALUE%']);

// Sprawdzenie implementacji interfejsu
if ($statusFilter instanceof FilterInterface) {
    echo "Klucz: " . $statusFilter->getKey() . "\n";
    echo "Typ: " . $statusFilter->getType() . "\n";
    echo "Tytuł: " . $statusFilter->getTitle() . "\n";
}
```

### Niestandardowy filtr zakresu

```php
<?php

class RangeFilter implements FilterInterface
{
    protected string $key = '';
    protected ?string $title = null;
    protected array $ranges = [];
    protected ?string $value = null;
    
    public static function create(string $key, ?string $type = null): self
    {
        return (new static())->setKey($key);
    }
    
    public function setRanges(array $ranges): self
    {
        $this->ranges = $ranges;
        return $this;
    }
    
    public function render(Table $table): string
    {
        $options = '<option value="%ALL%">Wszystkie zakresy</option>';
        
        foreach ($this->ranges as $key => $range) {
            $selected = $this->getValue() === $key ? 'selected' : '';
            $label = $range['label'];
            $options .= "<option value='{$key}' {$selected}>{$label}</option>";
        }
        
        return "
            <div class='range-filter'>
                <label>{$this->getTitle()}</label>
                <select name='filter-{$this->getKey()}' class='form-select'>
                    {$options}
                </select>
            </div>
        ";
    }
    
    public function getCondition(): array
    {
        if (!$this->value || $this->value === '%ALL%') {
            return [];
        }
        
        $range = $this->ranges[$this->value] ?? null;
        if (!$range) {
            return [];
        }
        
        $conditions = [];
        
        if (isset($range['min'])) {
            $conditions[] = new Condition($range['column'], '>=', $range['min']);
        }
        
        if (isset($range['max'])) {
            $conditions[] = new Condition($range['column'], '<=', $range['max']);
        }
        
        return $conditions;
    }
    
    // ... pozostałe implementacje metod interfejsu
}

// Użycie
$priceFilter = RangeFilter::create('price_range')
    ->setTitle('Zakres cen')
    ->setRanges([
        'cheap' => [
            'label' => 'Do 100 zł',
            'column' => 'products.price',
            'max' => 100
        ],
        'medium' => [
            'label' => '100-500 zł',
            'column' => 'products.price',
            'min' => 100,
            'max' => 500
        ],
        'expensive' => [
            'label' => 'Powyżej 500 zł',
            'column' => 'products.price',
            'min' => 500
        ]
    ]);
```

### Filtr z walidacją

```php
<?php

class ValidatedFilter implements FilterInterface
{
    protected array $validators = [];
    
    public function addValidator(callable $validator, string $message = 'Nieprawidłowa wartość'): self
    {
        $this->validators[] = ['validator' => $validator, 'message' => $message];
        return $this;
    }
    
    public function setValue(string $value): self
    {
        // Waliduj wartość
        foreach ($this->validators as $validatorData) {
            if (!$validatorData['validator']($value)) {
                // Można logować błąd lub rzucić wyjątek
                error_log("Filter validation failed: " . $validatorData['message']);
                return $this; // Nie ustawiaj nieprawidłowej wartości
            }
        }
        
        $this->value = $value;
        return $this;
    }
    
    public function render(Table $table): string
    {
        $value = htmlspecialchars($this->getValue() ?? '');
        
        return "
            <div class='validated-filter'>
                <label>{$this->getTitle()}</label>
                <input type='text' name='filter-{$this->getKey()}' 
                       value='{$value}' class='form-control'
                       placeholder='Wprowadź wartość...'>
                <small class='form-text text-muted'>
                    Wartość będzie zwalidowana
                </small>
            </div>
        ";
    }
    
    // ... pozostałe implementacje
}

// Użycie
$emailFilter = ValidatedFilter::create('email')
    ->setTitle('E-mail')
    ->addValidator(
        function($value) {
            return empty($value) || filter_var($value, FILTER_VALIDATE_EMAIL);
        },
        'Nieprawidłowy format e-mail'
    )
    ->setCondition(['users.email' => '%VALUE%']);
```

## Method Chaining

Interfejs wspiera method chaining:

```php
$filter = Filter::create('advanced_status', 'select')
    ->setTitle('Zaawansowany status')
    ->setContent([
        '%ALL%' => 'Wszystkie',
        'new' => 'Nowe',
        'processing' => 'W trakcie',
        'completed' => 'Zakończone'
    ])
    ->setCondition(['orders.status' => '%VALUE%']);
```

## Typy filtrów

### Select
- **Użycie**: Lista rozwijana z opcjami
- **Content**: Tablica klucz => wartość
- **Specjalne wartości**: `%ALL%` dla opcji "wszystkie"

### Date  
- **Użycie**: Pole daty HTML5
- **Content**: Nie używane
- **Format**: YYYY-MM-DD

### Checkbox
- **Użycie**: Pojedynczy checkbox
- **Content**: Nie używane
- **Wartości**: true/false, 1/0

## Wskazówki implementacji

1. **Factory method** - zawsze implementuj metodę `create()` jako statyczną
2. **Method chaining** - zwracaj `self` z metod setter
3. **Walidacja** - sprawdzaj poprawność danych
4. **Renderowanie** - używaj odpowiednich klas CSS Bootstrap
5. **Warunki** - obsługuj placeholder `%VALUE%`
6. **Specjalne wartości** - obsługuj `%ALL%` w filtrach select

## Testowanie implementacji

```php
<?php

function testFilterInterface(FilterInterface $filter): void
{
    // Test factory method
    $newFilter = $filter::create('test_key', 'select');
    assert($newFilter instanceof FilterInterface);
    assert($newFilter->getKey() === 'test_key');
    assert($newFilter->getType() === 'select');
    
    // Test method chaining
    $result = $filter->setTitle('Test Filter')
        ->setContent(['test' => 'Test Option'])
        ->setCondition(['field' => '%VALUE%']);
    assert($result instanceof FilterInterface);
    
    // Test getters
    assert(is_string($filter->getKey()));
    assert(is_array($filter->getCondition()));
    
    // Test setValue
    $filter->setValue('test_value');
    assert($filter->getValue() === 'test_value');
    
    echo "Wszystkie testy przeszły pomyślnie!\n";
}

// Test standardowej implementacji
testFilterInterface(Filter::create('test'));
```

## Zobacz także

- [Filter](../klasy/filter.md) - Standardowa implementacja FilterInterface
- [Table](../klasy/table.md) - Klasa Table używająca filtrów
- [TableInterface](table-interface.md) - Interfejs tabel
- [Przykłady z filtrami](../przykłady/tabela-z-filtrami.md)