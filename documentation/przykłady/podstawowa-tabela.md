# Podstawowa tabela

Ten przykład pokazuje, jak utworzyć prostą tabelę z podstawowymi funkcjami.

## Tabela ze statycznymi danymi

### Kod

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;

// Ustaw język na polski
Table::changeLanguage('pl');

// Utwórz tabelę z unikalnym ID
$table = new Table('users-basic');

// Dodaj kolumny
$table->addColumn(
    Column::create('id', 'ID')
        ->setStyle(['width' => '60px'])
);

$table->addColumn(
    Column::create('name', 'Imię i nazwisko')
        ->setSearch(true)
);

$table->addColumn(
    Column::create('email', 'E-mail')
        ->setSearch(true)
);

$table->addColumn(
    Column::create('phone', 'Telefon')
        ->setSearch(false)
        ->setStyle(['width' => '140px'])
);

$table->addColumn(
    Column::create('city', 'Miasto')
        ->setSearch(true)
);

// Ustaw dane
$table->setData([
    [
        'id' => 1,
        'name' => 'Jan Kowalski',
        'email' => 'jan.kowalski@example.com',
        'phone' => '+48 123 456 789',
        'city' => 'Warszawa'
    ],
    [
        'id' => 2,
        'name' => 'Anna Nowak',
        'email' => 'anna.nowak@example.com',
        'phone' => '+48 987 654 321',
        'city' => 'Kraków'
    ],
    [
        'id' => 3,
        'name' => 'Piotr Wiśniewski',
        'email' => 'piotr.wisniewski@example.com',
        'phone' => '+48 555 666 777',
        'city' => 'Gdańsk'
    ],
    [
        'id' => 4,
        'name' => 'Maria Wójcik',
        'email' => 'maria.wojcik@example.com',
        'phone' => '+48 111 222 333',
        'city' => 'Wrocław'
    ],
    [
        'id' => 5,
        'name' => 'Tomasz Kowalczyk',
        'email' => 'tomasz.kowalczyk@example.com',
        'phone' => '+48 444 555 666',
        'city' => 'Poznań'
    ]
]);

// Konfiguracja tabeli
$table->setLimit(3); // Pokaż 3 rekordy na stronę
$table->setLayout('normal'); // Użyj normalnego szablonu

// Wyrenderuj tabelę
echo $table->render();
```

### Wynik

Tabela wyświetli:
- 5 kolumn z danymi użytkowników
- Paginację (3 rekordy na stronę)
- Możliwość wyszukiwania w kolumnach: imię, email, miasto
- Sortowanie wszystkich kolumn
- Polski interfejs

## Tabela z modelem

### Kod

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;

// W kontrolerze
class UserController extends Controller
{
    public function index()
    {
        // Ustaw język
        Table::changeLanguage('pl');
        
        // Utwórz tabelę
        $table = new Table('users-model');
        
        // Załaduj model
        $userModel = $this->loadModel('User');
        $table->setModel($userModel);
        
        // Dodaj kolumny (używaj formatu tabela.kolumna)
        $table->addColumn(
            Column::create('users.id', 'ID')
                ->setStyle(['width' => '60px'])
        );
        
        $table->addColumn(
            Column::create('users.first_name', 'Imię')
        );
        
        $table->addColumn(
            Column::create('users.last_name', 'Nazwisko')
        );
        
        $table->addColumn(
            Column::create('users.email', 'E-mail')
        );
        
        $table->addColumn(
            Column::create('users.created_at', 'Data rejestracji')
                ->setValue(function($cell) {
                    $date = new DateTime($cell->value);
                    return $date->format('d.m.Y H:i');
                })
                ->setStyle(['width' => '140px'])
        );
        
        // Konfiguracja
        $table->setLimit(10);
        $table->setOrderBy('users.created_at DESC');
        
        // Przekaż do widoku
        $this->view->assign('userTable', $table->render());
        $this->view->render('users/index');
    }
}
```

### Model User

```php
<?php

use NimblePHP\Framework\Model;

class User extends Model
{
    protected string $tableName = 'users';
    
    protected array $fillable = [
        'first_name',
        'last_name', 
        'email',
        'phone',
        'city',
        'status'
    ];
}
```

### Widok (users/index.phtml)

```html
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista użytkowników</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Lista użytkowników</h1>
        
        <div class="row">
            <div class="col-12">
                <?= $userTable ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/table.js"></script>
</body>
</html>
```

## Tabela z niestandardowymi stylami

### Kod

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;

// Utwórz tabelę ze stylami
$table = new Table('styled-users');

// Dodaj niestandardowe klasy CSS
$table->setClass('table-striped table-hover');

// Kolumna z niestandardowym formatowaniem
$table->addColumn(
    Column::create('id', 'ID')
        ->setStyle([
            'width' => '60px',
            'text-align' => 'center',
            'font-weight' => 'bold'
        ])
);

$table->addColumn(
    Column::create('name', 'Nazwa użytkownika')
        ->setValue(function($cell) {
            return "<strong class='text-primary'>{$cell->value}</strong>";
        })
);

$table->addColumn(
    Column::create('status', 'Status')
        ->setValue(function($cell) {
            $status = $cell->value;
            $class = $status === 'active' ? 'success' : 'secondary';
            $text = $status === 'active' ? 'Aktywny' : 'Nieaktywny';
            
            return "<span class='badge bg-{$class}'>{$text}</span>";
        })
        ->setStyle(['text-align' => 'center'])
        ->setSearch(false)
);

$table->addColumn(
    Column::create('balance', 'Saldo')
        ->setValue(function($cell) {
            $balance = (float)$cell->value;
            $formatted = number_format($balance, 2, ',', ' ');
            $color = $balance >= 0 ? 'text-success' : 'text-danger';
            
            return "<span class='{$color}'>{$formatted} zł</span>";
        })
        ->setStyle([
            'text-align' => 'right',
            'width' => '120px'
        ])
);

// Dane przykładowe
$table->setData([
    ['id' => 1, 'name' => 'Jan Kowalski', 'status' => 'active', 'balance' => 1250.50],
    ['id' => 2, 'name' => 'Anna Nowak', 'status' => 'inactive', 'balance' => -150.25],
    ['id' => 3, 'name' => 'Piotr Wiśniewski', 'status' => 'active', 'balance' => 3500.00]
]);

echo $table->render();
```

## Tabela z akcjami

### Kod

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;

$table = new Table('users-with-actions');

// Standardowe kolumny
$table->addColumn(Column::create('id', 'ID'));
$table->addColumn(Column::create('name', 'Nazwa'));
$table->addColumn(Column::create('email', 'E-mail'));

// Kolumna akcji
$table->addColumn(
    Column::create('actions', 'Akcje')
        ->setValue(function($cell) {
            $id = $cell->data['id'];
            
            return "
                <div class='btn-group' role='group'>
                    <a href='/users/view/{$id}' class='btn btn-sm btn-outline-info' title='Zobacz'>
                        <i class='fas fa-eye'></i>
                    </a>
                    <a href='/users/edit/{$id}' class='btn btn-sm btn-outline-primary' title='Edytuj'>
                        <i class='fas fa-edit'></i>
                    </a>
                    <a href='/users/delete/{$id}' class='btn btn-sm btn-outline-danger' 
                       title='Usuń' onclick='return confirm(\"Czy na pewno chcesz usunąć tego użytkownika?\")'>
                        <i class='fas fa-trash'></i>
                    </a>
                </div>
            ";
        })
        ->setSearch(false)
        ->setSortable(false)
        ->setStyle(['width' => '120px', 'text-align' => 'center'])
);

// Dodaj akcje globalne
$table->addAction('Dodaj użytkownika', '/users/create', 'btn btn-success');
$table->addAction('Export CSV', '/users/export', 'btn btn-info');

// Dane
$table->setData([
    ['id' => 1, 'name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
    ['id' => 2, 'name' => 'Anna Nowak', 'email' => 'anna@example.com']
]);

echo $table->render();
```

## Konfiguracja zaawansowana

### Kod

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;

// Konfiguracja globalna
Table::changeLanguage('pl');
Table::$layout = 'modern';

$table = new Table('advanced-basic');

// Wyłącz sortowanie globalnie
$table->setSortable(false);

// Kolumny z różnymi ustawieniami
$table->addColumn(
    Column::create('id', 'ID')
        ->setSortable(true) // Włącz sortowanie tylko dla tej kolumny
        ->setStyle(['width' => '60px'])
);

$table->addColumn(
    Column::create('name', 'Nazwa')
        ->setSearch(true)
);

$table->addColumn(
    Column::create('description', 'Opis')
        ->setSearch(false)
        ->setValue(function($cell) {
            $desc = $cell->value;
            // Ogranicz długość opisu
            if (strlen($desc) > 50) {
                return substr($desc, 0, 47) . '...';
            }
            return $desc;
        })
);

// Ustawienia tabeli
$table->setLimit(5);
$table->setSearch('kowalski'); // Domyślne wyszukiwanie
$table->setClass('table-bordered');

// Dane
$table->setData([
    [
        'id' => 1, 
        'name' => 'Jan Kowalski', 
        'description' => 'Długi opis użytkownika, który zostanie skrócony w tabeli dla lepszej czytelności'
    ],
    [
        'id' => 2, 
        'name' => 'Anna Kowalska', 
        'description' => 'Krótki opis'
    ]
]);

echo $table->render();
```

## Wskazówki

1. **ID tabeli** musi być unikalne na stronie
2. **Klucze kolumn** dla modeli używaj w formacie `tabela.kolumna`
3. **Limit rekordów** ustaw odpowiednio do ilości danych
4. **Wyszukiwanie** włączaj tylko dla kolumn tekstowych
5. **Style** używaj do poprawy czytelności tabeli
6. **Akcje** dodawaj jako ostatnią kolumnę

## Zobacz także

- [Tabela z filtrami](tabela-z-filtrami.md)
- [Tabela AJAX](tabela-ajax.md)
- [Niestandardowe kolumny](niestandardowe-kolumny.md)
- [Klasa Table](../klasy/table.md)
- [Klasa Column](../klasy/column.md)