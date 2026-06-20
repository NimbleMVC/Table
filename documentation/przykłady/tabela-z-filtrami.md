# Tabela z filtrami

Ten przykład pokazuje, jak dodać różne typy filtrów do tabeli, umożliwiając użytkownikom zawężanie wyświetlanych danych.

## Podstawowa tabela z filtrami

### Kod

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;
use NimblePHP\Table\Filter;
use krzysztofzylka\DatabaseManager\Condition;

// W kontrolerze
class ProductController extends Controller
{
    public function index()
    {
        Table::changeLanguage('pl');
        
        // Utwórz tabelę
        $table = new Table('products-filtered');
        
        // Załaduj model
        $productModel = $this->loadModel('Product');
        $table->setModel($productModel);
        
        // Konfiguracja podstawowa
        $table->setLimit(15);
        $table->setLayout('modern');
        
        // Dodaj kolumny
        $table->addColumn(
            Column::create('products.id', 'ID')
                ->setStyle(['width' => '60px'])
        );
        
        $table->addColumn(
            Column::create('products.name', 'Nazwa produktu')
        );
        
        $table->addColumn(
            Column::create('products.price', 'Cena')
                ->setValue(function($cell) {
                    $price = number_format($cell->value, 2, ',', ' ');
                    return "{$price} zł";
                })
                ->setStyle(['text-align' => 'right', 'width' => '100px'])
        );
        
        $table->addColumn(
            Column::create('products.category', 'Kategoria')
        );
        
        $table->addColumn(
            Column::create('products.status', 'Status')
                ->setValue(function($cell) {
                    $status = $cell->value;
                    $badges = [
                        'active' => "<span class='badge bg-success'>Aktywny</span>",
                        'inactive' => "<span class='badge bg-secondary'>Nieaktywny</span>",
                        'draft' => "<span class='badge bg-warning'>Szkic</span>"
                    ];
                    return $badges[$status] ?? "<span class='badge bg-dark'>Nieznany</span>";
                })
                ->setSearch(false)
        );
        
        $table->addColumn(
            Column::create('products.created_at', 'Data dodania')
                ->setValue(function($cell) {
                    $date = new DateTime($cell->value);
                    return $date->format('d.m.Y');
                })
                ->setStyle(['width' => '120px'])
        );
        
        // FILTRY
        
        // 1. Filtr statusu (select)
        $statusFilter = Filter::create('status', 'select')
            ->setTitle('Status')
            ->setContent([
                '%ALL%' => 'Wszystkie',
                'active' => 'Aktywne',
                'inactive' => 'Nieaktywne',
                'draft' => 'Szkice'
            ])
            ->setCondition(['products.status' => '%VALUE%']);
        
        $table->addFilter($statusFilter);
        
        // 2. Filtr kategorii (select z danymi z bazy)
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
        
        $table->addFilter($categoryFilter);
        
        // 3. Filtr zakresu cen (select)
        $priceFilter = Filter::create('price_range', 'select')
            ->setTitle('Zakres cen')
            ->setContent([
                '%ALL%' => 'Wszystkie',
                '0-50' => 'Do 50 zł',
                '50-100' => '50-100 zł',
                '100-200' => '100-200 zł',
                '200+' => 'Powyżej 200 zł'
            ])
            ->setCondition([]);
        
        // Niestandardowa logika dla zakresu cen
        $priceFilter->setValue(function($value) {
            switch($value) {
                case '0-50':
                    return ['products.price' => new Condition('products.price', '<=', 50)];
                case '50-100':
                    return [
                        'products.price' => new Condition('products.price', '>=', 50),
                        'products.price' => new Condition('products.price', '<=', 100)
                    ];
                case '100-200':
                    return [
                        'products.price' => new Condition('products.price', '>=', 100),
                        'products.price' => new Condition('products.price', '<=', 200)
                    ];
                case '200+':
                    return ['products.price' => new Condition('products.price', '>', 200)];
                default:
                    return [];
            }
        });
        
        $table->addFilter($priceFilter);
        
        // 4. Filtr daty (date)
        $dateFilter = Filter::create('created_from', 'date')
            ->setTitle('Dodane po dacie')
            ->setCondition([
                'products.created_at' => new Condition('products.created_at', '>=', '%VALUE%')
            ]);
        
        $table->addFilter($dateFilter);
        
        // 5. Filtr checkbox
        $activeOnlyFilter = Filter::create('active_only', 'checkbox')
            ->setTitle('Tylko aktywne produkty')
            ->setCondition(['products.status' => 'active']);
        
        $table->addFilter($activeOnlyFilter);
        
        // Renderuj tabelę
        $this->view->assign('productTable', $table->render());
        $this->view->render('products/index');
    }
}
```

### Model Product

```php
<?php

use NimblePHP\Framework\Model;

class Product extends Model
{
    protected string $tableName = 'products';
    
    protected array $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'status',
        'stock_quantity'
    ];
    
    // Relacja z kategorią
    public function getWithCategory()
    {
        return $this->join('categories', 'products.category_id = categories.id');
    }
}
```

### Model Category

```php
<?php

use NimblePHP\Framework\Model;

class Category extends Model
{
    protected string $tableName = 'categories';
    
    protected array $fillable = [
        'name',
        'description',
        'active'
    ];
}
```

## Zaawansowane filtry

### Kod

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;
use NimblePHP\Table\Filter;
use krzysztofzylka\DatabaseManager\Condition;

class OrderController extends Controller
{
    public function index()
    {
        Table::changeLanguage('pl');
        
        $table = new Table('orders-advanced-filters');
        
        // Załaduj model
        $orderModel = $this->loadModel('Order');
        $table->setModel($orderModel);
        
        $table->setLimit(20);
        
        // Kolumny
        $table->addColumn(Column::create('orders.id', 'Nr zamówienia'));
        $table->addColumn(Column::create('orders.customer_name', 'Klient'));
        $table->addColumn(
            Column::create('orders.total_amount', 'Kwota')
                ->setValue(function($cell) {
                    return number_format($cell->value, 2, ',', ' ') . ' zł';
                })
        );
        $table->addColumn(Column::create('orders.status', 'Status'));
        $table->addColumn(
            Column::create('orders.created_at', 'Data zamówienia')
                ->setValue(function($cell) {
                    return (new DateTime($cell->value))->format('d.m.Y H:i');
                })
        );
        
        // ZAAWANSOWANE FILTRY
        
        // 1. Filtr statusu z ikonami
        $statusFilter = Filter::create('status', 'select')
            ->setTitle('Status zamówienia')
            ->setContent([
                '%ALL%' => '📋 Wszystkie',
                'pending' => '⏳ Oczekujące',
                'processing' => '🔄 W realizacji',
                'shipped' => '📦 Wysłane',
                'delivered' => '✅ Dostarczone',
                'cancelled' => '❌ Anulowane'
            ])
            ->setCondition(['orders.status' => '%VALUE%']);
        
        $table->addFilter($statusFilter);
        
        // 2. Filtr zakresu kwot
        $amountFilter = Filter::create('amount_range', 'select')
            ->setTitle('Kwota zamówienia')
            ->setContent([
                '%ALL%' => 'Wszystkie kwoty',
                'small' => 'Do 100 zł',
                'medium' => '100 - 500 zł',
                'large' => '500 - 1000 zł',
                'xlarge' => 'Powyżej 1000 zł'
            ])
            ->setCondition([]);
        
        // Niestandardowa logika dla kwot
        $amountFilter = $this->setupAmountFilter($amountFilter);
        $table->addFilter($amountFilter);
        
        // 3. Filtr dat - zakres
        $dateFromFilter = Filter::create('date_from', 'date')
            ->setTitle('Od daty')
            ->setCondition([
                'orders.created_at' => new Condition('orders.created_at', '>=', '%VALUE%')
            ]);
        
        $dateToFilter = Filter::create('date_to', 'date')
            ->setTitle('Do daty')
            ->setCondition([
                'orders.created_at' => new Condition('orders.created_at', '<=', '%VALUE% 23:59:59')
            ]);
        
        $table->addFilter($dateFromFilter);
        $table->addFilter($dateToFilter);
        
        // 4. Filtr typu płatności
        $paymentFilter = Filter::create('payment_method', 'select')
            ->setTitle('Metoda płatności')
            ->setContent([
                '%ALL%' => 'Wszystkie',
                'card' => '💳 Karta',
                'transfer' => '🏦 Przelew',
                'cash' => '💵 Gotówka',
                'paypal' => '🅿️ PayPal'
            ])
            ->setCondition(['orders.payment_method' => '%VALUE%']);
        
        $table->addFilter($paymentFilter);
        
        // 5. Filtr VIP klientów
        $vipFilter = Filter::create('vip_only', 'checkbox')
            ->setTitle('Tylko klienci VIP')
            ->setCondition(['customers.vip_status' => 1]);
        
        $table->addFilter($vipFilter);
        
        // 6. Filtr z wyszukiwaniem w nazwie klienta
        $customerFilter = Filter::create('customer_search', 'select')
            ->setTitle('Klient zawiera')
            ->setContent([
                '%ALL%' => 'Wszyscy',
                'kowalski' => 'Kowalski',
                'nowak' => 'Nowak',
                'company' => 'Firma'
            ])
            ->setCondition([
                'orders.customer_name' => new Condition('orders.customer_name', 'LIKE', '%%VALUE%%')
            ]);
        
        $table->addFilter($customerFilter);
        
        $this->view->assign('orderTable', $table->render());
        $this->view->render('orders/index');
    }
    
    private function setupAmountFilter($filter)
    {
        // Można też ustawić logikę w osobnej metodzie
        return $filter->setCondition([
            'small' => ['orders.total_amount' => new Condition('orders.total_amount', '<', 100)],
            'medium' => [
                'orders.total_amount' => new Condition('orders.total_amount', '>=', 100),
                'orders.total_amount' => new Condition('orders.total_amount', '<', 500)
            ],
            'large' => [
                'orders.total_amount' => new Condition('orders.total_amount', '>=', 500),
                'orders.total_amount' => new Condition('orders.total_amount', '<', 1000)
            ],
            'xlarge' => ['orders.total_amount' => new Condition('orders.total_amount', '>=', 1000)]
        ]);
    }
}
```

## Filtry z danymi ze związanych tabel

### Kod

```php
<?php

use NimblePHP\Table\Table;
use NimblePHP\Table\Column;
use NimblePHP\Table\Filter;

class UserController extends Controller
{
    public function index()
    {
        Table::changeLanguage('pl');
        
        $table = new Table('users-with-relations');
        
        // Model z joinami
        $userModel = $this->loadModel('User');
        $userModel->join('departments', 'users.department_id = departments.id', 'LEFT')
                  ->join('roles', 'users.role_id = roles.id', 'LEFT');
        
        $table->setModel($userModel);
        
        // Kolumny z joinowanych tabel
        $table->addColumn(Column::create('users.id', 'ID'));
        $table->addColumn(Column::create('users.name', 'Imię i nazwisko'));
        $table->addColumn(Column::create('departments.name', 'Dział'));
        $table->addColumn(Column::create('roles.name', 'Rola'));
        $table->addColumn(Column::create('users.status', 'Status'));
        
        // Filtry z joinowanych tabel
        
        // 1. Filtr działów
        $departmentModel = $this->loadModel('Department');
        $departments = $departmentModel->readAll(['departments.active' => 1]);
        
        $deptOptions = ['%ALL%' => 'Wszystkie działy'];
        foreach ($departments as $dept) {
            $deptOptions[$dept['departments']['id']] = $dept['departments']['name'];
        }
        
        $deptFilter = Filter::create('department', 'select')
            ->setTitle('Dział')
            ->setContent($deptOptions)
            ->setCondition(['users.department_id' => '%VALUE%']);
        
        $table->addFilter($deptFilter);
        
        // 2. Filtr ról
        $roleModel = $this->loadModel('Role');
        $roles = $roleModel->readAll();
        
        $roleOptions = ['%ALL%' => 'Wszystkie role'];
        foreach ($roles as $role) {
            $roleOptions[$role['roles']['id']] = $role['roles']['name'];
        }
        
        $roleFilter = Filter::create('role', 'select')
            ->setTitle('Rola')
            ->setContent($roleOptions)
            ->setCondition(['users.role_id' => '%VALUE%']);
        
        $table->addFilter($roleFilter);
        
        // 3. Filtr kombinowany (dział + rola)
        $combinedFilter = Filter::create('dept_role', 'select')
            ->setTitle('Dział + Rola')
            ->setContent([
                '%ALL%' => 'Wszystkie kombinacje',
                'it_admin' => 'IT + Administrator',
                'it_dev' => 'IT + Developer',
                'hr_manager' => 'HR + Manager',
                'sales_rep' => 'Sprzedaż + Przedstawiciel'
            ])
            ->setCondition([]);
        
        // Niestandardowa logika dla kombinacji
        $combinedFilter->setValue(function($value) {
            $combinations = [
                'it_admin' => [
                    'departments.name' => 'IT',
                    'roles.name' => 'Administrator'
                ],
                'it_dev' => [
                    'departments.name' => 'IT',
                    'roles.name' => 'Developer'
                ],
                'hr_manager' => [
                    'departments.name' => 'HR',
                    'roles.name' => 'Manager'
                ],
                'sales_rep' => [
                    'departments.name' => 'Sprzedaż',
                    'roles.name' => 'Przedstawiciel'
                ]
            ];
            
            return $combinations[$value] ?? [];
        });
        
        $table->addFilter($combinedFilter);
        
        $this->view->assign('userTable', $table->render());
        $this->view->render('users/index');
    }
}
```

## Filtry z walidacją i formatowaniem

### Kod

```php
<?php

use NimblePHP\Table\Filter;
use krzysztofzylka\DatabaseManager\Condition;

// Filtr dat z walidacją
$validatedDateFilter = Filter::create('valid_date', 'date')
    ->setTitle('Data (z walidacją)')
    ->setCondition([]);

$validatedDateFilter->setValue(function($value) {
    // Walidacja daty
    if (empty($value)) {
        return [];
    }
    
    try {
        $date = new DateTime($value);
        
        // Sprawdź czy data nie jest z przyszłości
        if ($date > new DateTime()) {
            return [];
        }
        
        // Sprawdź czy data nie jest starsza niż 5 lat
        $fiveYearsAgo = new DateTime('-5 years');
        if ($date < $fiveYearsAgo) {
            return [];
        }
        
        return [
            'orders.created_at' => new Condition('orders.created_at', '>=', $date->format('Y-m-d'))
        ];
        
    } catch (Exception $e) {
        return [];
    }
});

// Filtr z formatowaniem opcji
$formattedFilter = Filter::create('formatted_status', 'select')
    ->setTitle('Status (sformatowany)')
    ->setContent([
        '%ALL%' => '🔍 Pokaż wszystkie',
        'new' => '🆕 Nowe zamówienia',
        'processing' => '⚙️ W trakcie realizacji',
        'completed' => '✅ Zakończone pomyślnie',
        'failed' => '❌ Nieudane'
    ])
    ->setCondition(['orders.status' => '%VALUE%']);

// Filtr z dynamiczną zawartością na podstawie innego filtru
class DynamicFilterHelper
{
    public static function getCategoryProducts($categoryId)
    {
        if (empty($categoryId) || $categoryId === '%ALL%') {
            return ['%ALL%' => 'Wszystkie produkty'];
        }
        
        $productModel = new Product();
        $products = $productModel->readAll(['products.category_id' => $categoryId]);
        
        $options = ['%ALL%' => 'Wszystkie w kategorii'];
        foreach ($products as $product) {
            $options[$product['products']['id']] = $product['products']['name'];
        }
        
        return $options;
    }
}

// Użycie w JavaScript (w widoku)
?>
<script>
// Aktualizuj opcje produktów gdy zmieni się kategoria
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.querySelector('select[name="filter-category"]');
    const productFilter = document.querySelector('select[name="filter-product"]');
    
    if (categoryFilter && productFilter) {
        categoryFilter.addEventListener('change', function() {
            const categoryId = this.value;
            
            // AJAX call to update product options
            fetch(`/api/products-by-category/${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    productFilter.innerHTML = '';
                    Object.entries(data).forEach(([value, text]) => {
                        const option = document.createElement('option');
                        option.value = value;
                        option.textContent = text;
                        productFilter.appendChild(option);
                    });
                });
        });
    }
});
</script>
```

## Widok z filtrami

### HTML (products/index.phtml)

```html
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produkty z filtrami</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .filter-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: #495057;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-box"></i> 
                    Zarządzanie produktami
                </h1>
                
                <div class="table-container">
                    <?= $productTable ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-kit-id.js"></script>
    <script src="/assets/table.js"></script>
</body>
</html>
```

## Wskazówki dla filtrów

1. **Kolejność filtrów** - umieszczaj najważniejsze filtry jako pierwsze
2. **Opcja "Wszystkie"** - zawsze używaj `%ALL%` jako pierwszej opcji
3. **Nazwy opisowe** - używaj jasnych i zrozumiałych nazw dla opcji
4. **Walidacja** - sprawdzaj poprawność danych w filtrach
5. **Wydajność** - unikaj zbyt wielu filtrów na jednej tabeli
6. **UX** - grupuj powiązane filtry razem
7. **Responsywność** - filtry automatycznie dostosowują się do szerokości ekranu

## Zobacz także

- [Podstawowa tabela](podstawowa-tabela.md)
- [Tabela AJAX](tabela-ajax.md)
- [Klasa Filter](../klasy/filter.md)
- [Klasa Table](../klasy/table.md)