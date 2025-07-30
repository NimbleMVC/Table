# Szybki start

Ten przewodnik pomoże Ci szybko rozpocząć pracę z biblioteką NimblePHP Table.

## Podstawowa tabela

Najprostszy sposób utworzenia tabeli:

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;

// Utwórz instancję tabeli z unikalnym ID
$table = new Table('users-table');

// Dodaj kolumny
$table->addColumn(
    Column::create('id', 'ID')
);

$table->addColumn(
    Column::create('name', 'Imię')
);

$table->addColumn(
    Column::create('email', 'E-mail')
);

// Ustaw dane
$table->setData([
    ['id' => 1, 'name' => 'Jan Kowalski', 'email' => 'jan@example.com'],
    ['id' => 2, 'name' => 'Anna Nowak', 'email' => 'anna@example.com'],
    ['id' => 3, 'name' => 'Piotr Wiśniewski', 'email' => 'piotr@example.com']
]);

// Wyrenderuj tabelę
echo $table->render();
```

## Tabela z modelem

Jeśli używasz modeli NimblePHP, możesz bezpośrednio połączyć tabelę z modelem:

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;
use App\Models\User; // Twój model

$table = new Table('users-table');

// Ustaw model - dane będą pobierane automatycznie
$table->setModel(new User());

// Dodaj kolumny
$table->addColumn(
    Column::create('users.id', 'ID')
);

$table->addColumn(
    Column::create('users.name', 'Imię')
);

$table->addColumn(
    Column::create('users.email', 'E-mail')
);

echo $table->render();
```

## Konfiguracja podstawowa

### Ustawienie limitów i paginacji

```php
$table = new Table('users-table');

// Ustaw liczbę rekordów na stronę
$table->setLimit(10);

// Ustaw aktualną stronę
$table->setPage(1);

// Pozostała konfiguracja...
echo $table->render();
```

### Dodanie wyszukiwania

```php
$table = new Table('users-table');

// Ustaw domyślny tekst wyszukiwania
$table->setSearch('kowalski');

// Dodaj kolumny z możliwością wyszukiwania
$table->addColumn(
    Column::create('name', 'Imię')
        ->setSearch(true) // Domyślnie true
);

$table->addColumn(
    Column::create('email', 'E-mail')
        ->setSearch(false) // Wyłącz wyszukiwanie dla tej kolumny
);

echo $table->render();
```

## Niestandardowe kolumny

Możesz definiować własne funkcje dla kolumn:

```php
$table->addColumn(
    Column::create('status', 'Status')
        ->setValue(function($cell) {
            $status = $cell->value;
            $class = $status === 'active' ? 'success' : 'danger';
            return "<span class='badge bg-{$class}'>{$status}</span>";
        })
);

$table->addColumn(
    Column::create('actions', 'Akcje')
        ->setValue(function($cell) {
            $id = $cell->data['id'];
            return "
                <a href='/edit/{$id}' class='btn btn-sm btn-primary'>Edytuj</a>
                <a href='/delete/{$id}' class='btn btn-sm btn-danger'>Usuń</a>
            ";
        })
        ->setSearch(false) // Wyłącz wyszukiwanie dla kolumny akcji
        ->setSortable(false) // Wyłącz sortowanie
);
```

## Stylizacja kolumn

```php
$table->addColumn(
    Column::create('price', 'Cena')
        ->setStyle([
            'text-align' => 'right',
            'font-weight' => 'bold',
            'color' => '#28a745'
        ])
);
```

## Zmiana języka

```php
// Ustaw język polski
Table::changeLanguage('pl');

// Lub ustaw własne tłumaczenia
Table::$LANGUAGE = [
    'search' => 'Szukaj...',
    'show' => 'Pokaż',
    'records' => 'rekordów',
    'page' => 'Strona',
    'of' => 'z',
    'empty_data' => 'Brak danych'
];
```

## Wybór szablonu

```php
// Ustaw globalny szablon dla wszystkich tabel
Table::$layout = 'modern'; // normal, professional, modern

// Lub ustaw szablon dla konkretnej tabeli
$table->setLayout('professional');
```

## Dodanie filtrów

```php
use NimblePHP\Table\Filter;

$table = new Table('users-table');

// Filtr select
$statusFilter = Filter::create('status', 'select')
    ->setTitle('Status')
    ->setContent([
        '%ALL%' => 'Wszystkie',
        'active' => 'Aktywni',
        'inactive' => 'Nieaktywni'
    ])
    ->setCondition(['users.status' => '%VALUE%']);

$table->addFilter($statusFilter);

// Filtr daty
$dateFilter = Filter::create('created_date', 'date')
    ->setTitle('Data utworzenia')
    ->setCondition(['users.created_at' => new Condition('users.created_at', '>=', '%VALUE%')]);

$table->addFilter($dateFilter);
```

## Tryb AJAX

```php
$table = new Table('users-table');
$table->setModel(new User());
$table->setAjax(true); // Włącz tryb AJAX

// Ustaw klucz AJAX (dla rozróżnienia użytkowników)
Table::setAjaxKey(session_id());

// Pozostała konfiguracja...
echo $table->render();
```

## Pełny przykład

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;
use NimblePHP\Table\Filter;
use App\Models\User;

// Ustaw język
Table::changeLanguage('pl');

// Utwórz tabelę
$table = new Table('advanced-users-table');
$table->setModel(new User());
$table->setAjax(true);
$table->setLimit(15);
$table->setLayout('modern');

// Dodaj kolumny
$table->addColumn(
    Column::create('users.id', 'ID')
        ->setStyle(['width' => '60px'])
);

$table->addColumn(
    Column::create('users.name', 'Imię i nazwisko')
);

$table->addColumn(
    Column::create('users.email', 'E-mail')
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

// Ustaw klucz AJAX
Table::setAjaxKey(session_id());

// Wyrenderuj tabelę
echo $table->render();
```

## Następne kroki

Teraz gdy znasz podstawy, możesz:

1. Przejść do szczegółowej dokumentacji [klasy Table](klasy/table.md)
2. Poznać więcej [przykładów użycia](przykłady/)
3. Sprawdzić wszystkie dostępne [interfejsy](interfejsy/)

## Wskazówki

- **ID tabeli** powinno być unikalne na stronie
- **Klucze kolumn** dla modeli używaj w formacie `tabela.kolumna`
- **Funkcje niestandardowe** w kolumnach otrzymują obiekt `Cell` z właściwościami `value` i `data`
- **Tryb AJAX** wymaga włączonej bazy danych i modułu migrations
- **Filtry** używają symbolu `%VALUE%` jako placeholder dla wartości