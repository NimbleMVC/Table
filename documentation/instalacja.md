# Instalacja

## Wymagania

Przed instalacją upewnij się, że spełniasz następujące wymagania:

- **PHP** >= 8.2
- **NimblePHP Framework** >= 0.2.0
- **Composer** - menedżer pakietów PHP

## Instalacja przez Composer

Zainstaluj bibliotekę używając Composer:

```bash
composer require nimblephp/table
```

## Konfiguracja

### 1. Rejestracja Service Provider

Biblioteka automatycznie rejestruje swój `ServiceProvider`, który:
- Uruchamia migracje bazy danych (jeśli moduł migrations jest włączony)
- Kopiuje pliki JavaScript do katalogu `public/assets/`
- Dodaje skrypty JavaScript do Twig (jeśli moduł Twig jest włączony)

### 2. Baza danych (opcjonalnie)

Jeśli chcesz używać funkcji AJAX z zachowywaniem stanu tabeli, musisz mieć włączoną bazę danych i moduł migrations:

```bash
composer require nimblephp/migrations
```

Ustaw zmienną środowiskową:
```env
DATABASE=true
```

### 3. Pliki statyczne

Po instalacji biblioteka automatycznie skopiuje plik `table.js` do katalogu `public/assets/`. Upewnij się, że katalog jest dostępny dla przeglądarki.

### 4. Twig (opcjonalnie)

Jeśli używasz modułu Twig, skrypty JavaScript zostaną automatycznie dodane do nagłówka:

```bash
composer require nimblephp/twig
```

## Weryfikacja instalacji

Aby sprawdzić, czy instalacja przebiegła pomyślnie, utwórz prostą tabelę:

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;

$table = new Table('test-table');

$table->addColumn(
    Column::create('id', 'ID')
);

$table->addColumn(
    Column::create('name', 'Nazwa')
);

$table->setData([
    ['id' => 1, 'name' => 'Test 1'],
    ['id' => 2, 'name' => 'Test 2']
]);

echo $table->render();
```

### Weryfikacja z modelem

Jeśli chcesz przetestować z modelem:

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;

// W kontrolerze
$table = new Table('test-model-table');

// Załaduj model
$userModel = $this->loadModel('User');
$table->setModel($userModel);

$table->addColumn(
    Column::create('users.id', 'ID')
);

$table->addColumn(
    Column::create('users.name', 'Nazwa')
);

echo $table->render();
```

Jeśli tabela wyświetla się poprawnie, instalacja przebiegła pomyślnie.

## Rozwiązywanie problemów

### Problem: Brak pliku table.js

**Rozwiązanie**: Upewnij się, że katalog `public/assets/` istnieje i ma odpowiednie uprawnienia do zapisu.

### Problem: Błąd bazy danych w trybie AJAX

**Rozwiązanie**: 
1. Sprawdź, czy zmienna `DATABASE` jest ustawiona na `true`
2. Upewnij się, że moduł `nimblephp/migrations` jest zainstalowany
3. Sprawdź połączenie z bazą danych

### Problem: Brak stylów CSS

**Rozwiązanie**: Biblioteka używa klas Bootstrap. Upewnij się, że Bootstrap CSS jest włączony w Twojej aplikacji:

```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
```

## Następne kroki

Po pomyślnej instalacji przejdź do [Szybkiego startu](szybki-start.md), aby nauczyć się podstaw używania biblioteki.