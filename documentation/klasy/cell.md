# Klasa Cell

Klasa `Cell` reprezentuje pojedynczą komórkę tabeli i jest używana w funkcjach callback kolumn. Zawiera wartość komórki oraz pełne dane wiersza, umożliwiając tworzenie zaawansowanych i dynamicznych kolumn.

## Namespace

```php
NimblePHP\Table\Cell
```

## Właściwości

### `$value`
```php
public mixed $value
```
Wartość komórki pobrana z danych na podstawie klucza kolumny.

### `$data`
```php
public array $data = []
```
Pełne dane wiersza tabeli jako tablica.

## Opis

Klasa `Cell` nie posiada metod - jest prostym kontenerem danych przekazywanym do funkcji callback w kolumnach. Obiekt jest automatycznie tworzony przez tabelę podczas renderowania każdej komórki.

## Struktura danych

### Dla modeli (z bazą danych)

Gdy tabela używa modelu, struktura danych w `$cell->data` zawiera tablice zagnieżdżone według nazw tabel:

```php
$cell->data = [
    'users' => [
        'id' => 1,
        'name' => 'Jan Kowalski',
        'email' => 'jan@example.com',
        'status' => 'active',
        'created_at' => '2023-01-15 10:30:00'
    ],
    // Ewentualne dane z joinów
    'profiles' => [
        'avatar' => 'avatar.jpg',
        'bio' => 'Opis użytkownika'
    ]
];
```

### Dla zwykłych tablic

Gdy tabela używa zwykłych danych (bez modelu), struktura jest płaska:

```php
$cell->data = [
    'id' => 1,
    'name' => 'Jan Kowalski',
    'email' => 'jan@example.com',
    'status' => 'active',
    'created_at' => '2023-01-15 10:30:00'
];
```

## Przykłady użycia

### Podstawowe użycie w funkcji callback

```php
$column = Column::create('name', 'Imię')
    ->setValue(function($cell) {
        // $cell->value zawiera wartość z klucza 'name'
        return strtoupper($cell->value);
    });
```

### Dostęp do innych danych wiersza (model)

```php
$column = Column::create('actions', 'Akcje')
    ->setValue(function($cell) {
        // Pobierz ID z danych wiersza
        $id = $cell->data['users']['id'];
        $name = $cell->data['users']['name'];
        
        return "
            <a href='/users/edit/{$id}' class='btn btn-sm btn-primary'>
                Edytuj {$name}
            </a>
        ";
    });
```

### Dostęp do innych danych wiersza (tablica)

```php
$column = Column::create('actions', 'Akcje')
    ->setValue(function($cell) {
        // Pobierz ID z danych wiersza
        $id = $cell->data['id'];
        $name = $cell->data['name'];
        
        return "
            <a href='/users/edit/{$id}' class='btn btn-sm btn-primary'>
                Edytuj {$name}
            </a>
        ";
    });
```

### Warunkowe formatowanie na podstawie innych pól

```php
$column = Column::create('status', 'Status')
    ->setValue(function($cell) {
        $status = $cell->value;
        $isAdmin = $cell->data['users']['role'] === 'admin';
        
        // Różne style dla adminów
        if ($isAdmin) {
            $class = $status === 'active' ? 'success' : 'warning';
            $prefix = '[ADMIN] ';
        } else {
            $class = $status === 'active' ? 'primary' : 'secondary';
            $prefix = '';
        }
        
        return "<span class='badge bg-{$class}'>{$prefix}{$status}</span>";
    });
```

### Złożone obliczenia

```php
$column = Column::create('total_score', 'Wynik')
    ->setValue(function($cell) {
        // Oblicz wynik na podstawie kilku pól
        $score1 = (int)$cell->data['users']['score1'];
        $score2 = (int)$cell->data['users']['score2'];
        $bonus = (int)$cell->data['users']['bonus'];
        
        $total = $score1 + $score2 + $bonus;
        $average = $total / 3;
        
        $color = $average >= 80 ? 'success' : ($average >= 60 ? 'warning' : 'danger');
        
        return "
            <div>
                <strong class='text-{$color}'>{$total} pkt</strong><br>
                <small class='text-muted'>Średnia: " . number_format($average, 1) . "</small>
            </div>
        ";
    });
```

### Formatowanie dat

```php
$column = Column::create('created_at', 'Data utworzenia')
    ->setValue(function($cell) {
        $date = new DateTime($cell->value);
        $now = new DateTime();
        $diff = $now->diff($date);
        
        // Pokaż względny czas dla ostatnich 7 dni
        if ($diff->days <= 7) {
            if ($diff->days == 0) {
                $relative = 'Dzisiaj';
            } elseif ($diff->days == 1) {
                $relative = 'Wczoraj';
            } else {
                $relative = $diff->days . ' dni temu';
            }
            
            return "
                <div>
                    <strong>{$relative}</strong><br>
                    <small class='text-muted'>" . $date->format('H:i') . "</small>
                </div>
            ";
        } else {
            return $date->format('d.m.Y H:i');
        }
    });
```

### Tworzenie linków z parametrami

```php
$column = Column::create('name', 'Nazwa')
    ->setValue(function($cell) {
        $id = $cell->data['users']['id'];
        $name = $cell->value;
        $status = $cell->data['users']['status'];
        
        // Różne linki w zależności od statusu
        if ($status === 'active') {
            $url = "/users/profile/{$id}";
            $class = "text-success";
        } else {
            $url = "/users/activate/{$id}";
            $class = "text-muted";
        }
        
        return "<a href='{$url}' class='{$class}'>{$name}</a>";
    });
```

### Obsługa pustych wartości

```php
$column = Column::create('description', 'Opis')
    ->setValue(function($cell) {
        $description = trim($cell->value ?? '');
        
        if (empty($description)) {
            return "<em class='text-muted'>Brak opisu</em>";
        }
        
        // Ogranicz długość opisu
        if (strlen($description) > 100) {
            $short = substr($description, 0, 97) . '...';
            return "<span title='{$description}'>{$short}</span>";
        }
        
        return $description;
    });
```

### Tworzenie list z danych JSON

```php
$column = Column::create('tags', 'Tagi')
    ->setValue(function($cell) {
        $tagsJson = $cell->value;
        
        if (empty($tagsJson)) {
            return "<em class='text-muted'>Brak tagów</em>";
        }
        
        $tags = json_decode($tagsJson, true);
        
        if (!is_array($tags)) {
            return "<em class='text-muted'>Błędne dane</em>";
        }
        
        $badges = array_map(function($tag) {
            return "<span class='badge bg-secondary me-1'>{$tag}</span>";
        }, $tags);
        
        return implode('', $badges);
    });
```

### Wyświetlanie zdjęć z fallback

```php
$column = Column::create('avatar', 'Zdjęcie')
    ->setValue(function($cell) {
        $avatar = $cell->value;
        $name = $cell->data['users']['name'] ?? 'User';
        $initials = strtoupper(substr($name, 0, 1));
        
        if (!empty($avatar) && file_exists(public_path($avatar))) {
            return "
                <img src='{$avatar}' alt='{$name}' 
                     class='rounded-circle' width='40' height='40'
                     onerror='this.style.display=\"none\"; this.nextElementSibling.style.display=\"flex\";'>
                <div class='rounded-circle bg-primary text-white d-flex align-items-center justify-content-center' 
                     style='width: 40px; height: 40px; display: none;'>
                    {$initials}
                </div>
            ";
        } else {
            return "
                <div class='rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center' 
                     style='width: 40px; height: 40px;'>
                    {$initials}
                </div>
            ";
        }
    });
```

## Bezpieczne pobieranie danych

### Dla modeli

```php
$column->setValue(function($cell) {
    // Bezpieczne pobieranie z zagnieżdżonych tablic
    $userId = $cell->data['users']['id'] ?? null;
    $profileAvatar = $cell->data['profiles']['avatar'] ?? null;
    
    if (!$userId) {
        return 'Błąd: brak ID użytkownika';
    }
    
    // Dalsze przetwarzanie...
});
```

### Dla zwykłych tablic

```php
$column->setValue(function($cell) {
    // Sprawdzenie istnienia klucza
    if (!isset($cell->data['id'])) {
        return 'Błąd: brak ID';
    }
    
    $id = $cell->data['id'];
    $name = $cell->data['name'] ?? 'Nieznany';
    
    // Dalsze przetwarzanie...
});
```

## Uniwersalna funkcja pomocnicza

Możesz utworzyć funkcję pomocniczą do bezpiecznego pobierania danych:

```php
function getCellValue($cell, $key, $default = null) {
    // Dla modeli (zagnieżdżone tablice)
    if (strpos($key, '.') !== false) {
        $parts = explode('.', $key);
        $data = $cell->data;
        
        foreach ($parts as $part) {
            if (!isset($data[$part])) {
                return $default;
            }
            $data = $data[$part];
        }
        
        return $data;
    }
    
    // Dla zwykłych tablic
    return $cell->data[$key] ?? $default;
}

// Użycie:
$column->setValue(function($cell) {
    $id = getCellValue($cell, 'users.id', 0);
    $name = getCellValue($cell, 'users.name', 'Nieznany');
    
    return "<a href='/user/{$id}'>{$name}</a>";
});
```

## Debugowanie

Aby sprawdzić strukturę danych w komórce:

```php
$column->setValue(function($cell) {
    // Debugowanie - usuń w produkcji
    error_log('Cell value: ' . print_r($cell->value, true));
    error_log('Cell data: ' . print_r($cell->data, true));
    
    return $cell->value;
});
```

## Wskazówki

1. **Zawsze sprawdzaj** istnienie danych przed ich użyciem
2. **Używaj operatora ??** do ustawiania wartości domyślnych
3. **Escapuj dane HTML** gdy wyświetlasz dane użytkownika
4. **Ogranicz złożoność** funkcji callback dla lepszej wydajności
5. **Testuj różne scenariusze** z pustymi i błędnymi danymi

## Zobacz także

- [Column](column.md) - Dokumentacja klasy Column
- [Table](table.md) - Dokumentacja klasy Table
- [Przykłady niestandardowych kolumn](../przykłady/niestandardowe-kolumny.md)