# Tabela AJAX

Ten przykład pokazuje, jak utworzyć tabelę z obsługą AJAX, która dynamicznie odświeża zawartość bez przeładowania strony.

## Podstawowa tabela AJAX

### Kontroler

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
        
        // Utwórz tabelę AJAX
        $table = new Table('users-ajax-table');
        
        // Załaduj model
        $userModel = $this->loadModel('User');
        $table->setModel($userModel);
        
        // WŁĄCZ TRYB AJAX
        $table->setAjax(true);
        
        // Ustaw klucz AJAX (ważne dla rozróżnienia użytkowników)
        Table::setAjaxKey(session_id());
        
        // Konfiguracja podstawowa
        $table->setLimit(20);
        $table->setLayout('modern');
        $table->setOrderBy('users.created_at DESC');
        
        // Dodaj kolumny
        $table->addColumn(
            Column::create('users.id', 'ID')
                ->setStyle(['width' => '60px'])
        );
        
        $table->addColumn(
            Column::create('users.name', 'Imię i nazwisko')
                ->setSearch(true)
        );
        
        $table->addColumn(
            Column::create('users.email', 'E-mail')
                ->setSearch(true)
        );
        
        $table->addColumn(
            Column::create('users.status', 'Status')
                ->setValue(function($cell) {
                    $status = $cell->value;
                    $badges = [
                        'active' => "<span class='badge bg-success'>Aktywny</span>",
                        'inactive' => "<span class='badge bg-secondary'>Nieaktywny</span>",
                        'banned' => "<span class='badge bg-danger'>Zablokowany</span>"
                    ];
                    return $badges[$status] ?? "<span class='badge bg-dark'>Nieznany</span>";
                })
                ->setSearch(false)
        );
        
        $table->addColumn(
            Column::create('users.last_login', 'Ostatnie logowanie')
                ->setValue(function($cell) {
                    if (empty($cell->value)) {
                        return '<em class="text-muted">Nigdy</em>';
                    }
                    
                    $date = new DateTime($cell->value);
                    $now = new DateTime();
                    $diff = $now->diff($date);
                    
                    if ($diff->days == 0) {
                        return 'Dzisiaj o ' . $date->format('H:i');
                    } elseif ($diff->days == 1) {
                        return 'Wczoraj o ' . $date->format('H:i');
                    } else {
                        return $date->format('d.m.Y H:i');
                    }
                })
                ->setStyle(['width' => '150px'])
        );
        
        $table->addColumn(
            Column::create('actions', 'Akcje')
                ->setValue(function($cell) {
                    $id = $cell->data['users']['id'];
                    $status = $cell->data['users']['status'];
                    
                    $actions = "
                        <div class='btn-group' role='group'>
                            <a href='/users/view/{$id}' class='btn btn-sm btn-outline-info' title='Zobacz'>
                                <i class='fas fa-eye'></i>
                            </a>
                            <a href='/users/edit/{$id}' class='btn btn-sm btn-outline-primary' title='Edytuj'>
                                <i class='fas fa-edit'></i>
                            </a>
                    ";
                    
                    if ($status !== 'banned') {
                        $actions .= "
                            <button class='btn btn-sm btn-outline-warning ajax-action-btn' 
                                    data-action='ban' data-id='{$id}' title='Zablokuj'>
                                <i class='fas fa-ban'></i>
                            </button>
                        ";
                    } else {
                        $actions .= "
                            <button class='btn btn-sm btn-outline-success ajax-action-btn' 
                                    data-action='unban' data-id='{$id}' title='Odblokuj'>
                                <i class='fas fa-check'></i>
                            </button>
                        ";
                    }
                    
                    $actions .= "
                            <button class='btn btn-sm btn-outline-danger ajax-action-btn' 
                                    data-action='delete' data-id='{$id}' title='Usuń'
                                    onclick='return confirm(\"Czy na pewno chcesz usunąć tego użytkownika?\")'>
                                <i class='fas fa-trash'></i>
                            </button>
                        </div>
                    ";
                    
                    return $actions;
                })
                ->setSearch(false)
                ->setSortable(false)
                ->setStyle(['width' => '150px'])
        );
        
        // Dodaj filtry AJAX
        $statusFilter = Filter::create('status', 'select')
            ->setTitle('Status')
            ->setContent([
                '%ALL%' => 'Wszystkie',
                'active' => 'Aktywni',
                'inactive' => 'Nieaktywni',
                'banned' => 'Zablokowani'
            ])
            ->setCondition(['users.status' => '%VALUE%']);
        
        $table->addFilter($statusFilter);
        
        $dateFilter = Filter::create('registration_date', 'date')
            ->setTitle('Zarejestrowany po')
            ->setCondition([
                'users.created_at' => new Condition('users.created_at', '>=', '%VALUE%')
            ]);
        
        $table->addFilter($dateFilter);
        
        // Dodaj akcje globalne
        $table->addAction('Dodaj użytkownika', '/users/create', 'btn btn-success');
        $table->addAction('Export CSV', '/users/export', 'btn btn-info');
        
        // Renderuj tabelę
        $this->view->assign('userTable', $table->render());
        $this->view->render('users/index');
    }
    
    // Endpoint dla akcji AJAX
    public function ajaxAction()
    {
        if (!$this->request->isPost()) {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $action = $this->request->getPost('action');
        $userId = (int)$this->request->getPost('id');
        
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid user ID']);
            return;
        }
        
        $userModel = $this->loadModel('User');
        
        try {
            switch ($action) {
                case 'ban':
                    $userModel->setId($userId)->update(['status' => 'banned']);
                    echo json_encode(['success' => true, 'message' => 'Użytkownik został zablokowany']);
                    break;
                    
                case 'unban':
                    $userModel->setId($userId)->update(['status' => 'active']);
                    echo json_encode(['success' => true, 'message' => 'Użytkownik został odblokowany']);
                    break;
                    
                case 'delete':
                    $userModel->setId($userId)->delete();
                    echo json_encode(['success' => true, 'message' => 'Użytkownik został usunięty']);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Unknown action']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
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
        'name',
        'email',
        'password',
        'status',
        'last_login',
        'created_at',
        'updated_at'
    ];
    
    // Metoda do aktualizacji ostatniego logowania
    public function updateLastLogin(int $userId): bool
    {
        return $this->setId($userId)->update([
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }
    
    // Metoda do zmiany statusu
    public function changeStatus(int $userId, string $status): bool
    {
        $allowedStatuses = ['active', 'inactive', 'banned'];
        
        if (!in_array($status, $allowedStatuses)) {
            throw new InvalidArgumentException('Invalid status');
        }
        
        return $this->setId($userId)->update(['status' => $status]);
    }
}
```

### Widok (users/index.phtml)

```html
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie użytkownikami - AJAX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .ajax-loading {
            position: relative;
        }
        
        .ajax-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        
        .ajax-loading::before {
            content: 'Ładowanie...';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 11;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        
        .btn-group .btn {
            margin: 0 1px;
        }
        
        .ajax-action-btn:disabled {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>
                        <i class="fas fa-users text-primary"></i>
                        Zarządzanie użytkownikami
                        <small class="text-muted">(AJAX)</small>
                    </h1>
                    
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary" onclick="refreshTable()">
                            <i class="fas fa-refresh"></i> Odśwież
                        </button>
                        <button class="btn btn-outline-info" onclick="exportData()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                
                <div class="table-container" id="table-container">
                    <?= $userTable ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast container for notifications -->
    <div class="toast-container"></div>
    
    <!-- Loading overlay template -->
    <div id="loading-overlay" class="d-none">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Ładowanie...</span>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/table.js"></script>
    <script>
        // Rozszerzone funkcjonalności AJAX
        document.addEventListener('DOMContentLoaded', function() {
            // Obsługa akcji AJAX
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('ajax-action-btn') || 
                    e.target.closest('.ajax-action-btn')) {
                    
                    const button = e.target.classList.contains('ajax-action-btn') 
                        ? e.target 
                        : e.target.closest('.ajax-action-btn');
                    
                    const action = button.dataset.action;
                    const userId = button.dataset.id;
                    
                    if (!action || !userId) return;
                    
                    // Wyłącz przycisk podczas wykonywania akcji
                    button.disabled = true;
                    
                    // Wykonaj akcję AJAX
                    performAjaxAction(action, userId, button);
                }
            });
            
            // Nasłuchuj na zmiany w tabeli (po odświeżeniu AJAX)
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        // Tabela została odświeżona - możemy dodać dodatkową logikę
                        console.log('Tabela została odświeżona');
                    }
                });
            });
            
            observer.observe(document.getElementById('table-container'), {
                childList: true,
                subtree: true
            });
        });
        
        // Funkcja do wykonywania akcji AJAX
        function performAjaxAction(action, userId, button) {
            fetch('/users/ajax-action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=${action}&id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    
                    // Odśwież tabelę po pomyślnej akcji
                    setTimeout(() => {
                        refreshTable();
                    }, 1000);
                } else {
                    showToast(data.error || 'Wystąpił błąd', 'error');
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Wystąpił błąd połączenia', 'error');
                button.disabled = false;
            });
        }
        
        // Funkcja do odświeżania tabeli
        function refreshTable() {
            const tableContainer = document.getElementById('table-container');
            tableContainer.classList.add('ajax-loading');
            
            // Symuluj kliknięcie w tabelę, aby wywołać odświeżenie AJAX
            const form = tableContainer.querySelector('form');
            if (form) {
                const formData = new FormData(form);
                formData.append('table_action_id', form.querySelector('[name="table_action_id"]').value);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    // Parsuj odpowiedź i zaktualizuj tabelę
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.querySelector('.table-container').innerHTML;
                    
                    tableContainer.innerHTML = newTable;
                    tableContainer.classList.remove('ajax-loading');
                })
                .catch(error => {
                    console.error('Refresh error:', error);
                    tableContainer.classList.remove('ajax-loading');
                });
            }
        }
        
        // Funkcja do eksportu danych
        function exportData() {
            showToast('Rozpoczynam eksport danych...', 'info');
            window.location.href = '/users/export';
        }
        
        // Funkcja do wyświetlania powiadomień toast
        function showToast(message, type = 'info') {
            const toastContainer = document.querySelector('.toast-container');
            const toastId = 'toast-' + Date.now();
            
            const bgClass = {
                'success': 'bg-success',
                'error': 'bg-danger',
                'warning': 'bg-warning',
                'info': 'bg-info'
            }[type] || 'bg-info';
            
            const toastHtml = `
                <div id="${toastId}" class="toast ${bgClass} text-white" role="alert">
                    <div class="toast-header ${bgClass} text-white border-0">
                        <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        <strong class="me-auto">Powiadomienie</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: type === 'error' ? 5000 : 3000
            });
            
            toast.show();
            
            // Usuń toast po ukryciu
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }
        
        // Obsługa błędów AJAX globalnie
        window.addEventListener('unhandledrejection', function(event) {
            console.error('Unhandled promise rejection:', event.reason);
            showToast('Wystąpił nieoczekiwany błąd', 'error');
        });
        
        // Pokaż powiadomienie o załadowaniu strony
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                showToast('Tabela AJAX została załadowana', 'success');
            }, 500);
        });
    </script>
</body>
</html>
```

## Zaawansowana tabela AJAX z akcjami masowymi

### Kontroler z akcjami masowymi

```php
<?php

class UserController extends Controller
{
    public function advancedAjaxTable()
    {
        Table::changeLanguage('pl');
        
        $table = new Table('users-advanced-ajax');
        
        // Załaduj model
        $userModel = $this->loadModel('User');
        $table->setModel($userModel);
        
        // Konfiguracja AJAX
        $table->setAjax(true);
        $table->setAjaxAction(true); // Włącz akcje AJAX (checkboxy)
        $table->setAjaxActionKey('users.id'); // Klucz dla akcji
        
        Table::setAjaxKey(session_id());
        
        // Podstawowa konfiguracja
        $table->setLimit(25);
        $table->setLayout('modern');
        
        // Kolumny (pierwsza kolumna z checkboxami zostanie dodana automatycznie)
        $table->addColumn(
            Column::create('users.id', 'ID')
                ->setStyle(['width' => '60px'])
        );
        
        $table->addColumn(
            Column::create('users.name', 'Nazwa użytkownika')
                ->setSearch(true)
        );
        
        $table->addColumn(
            Column::create('users.email', 'E-mail')
                ->setSearch(true)
        );
        
        $table->addColumn(
            Column::create('users.role', 'Rola')
                ->setValue(function($cell) {
                    $role = $cell->value;
                    $badges = [
                        'admin' => "<span class='badge bg-danger'>Administrator</span>",
                        'moderator' => "<span class='badge bg-warning'>Moderator</span>",
                        'user' => "<span class='badge bg-primary'>Użytkownik</span>"
                    ];
                    return $badges[$role] ?? "<span class='badge bg-secondary'>{$role}</span>";
                })
        );
        
        $table->addColumn(
            Column::create('users.created_at', 'Data rejestracji')
                ->setValue(function($cell) {
                    return (new DateTime($cell->value))->format('d.m.Y H:i');
                })
                ->setStyle(['width' => '140px'])
        );
        
        // Filtry
        $roleFilter = Filter::create('role', 'select')
            ->setTitle('Rola')
            ->setContent([
                '%ALL%' => 'Wszystkie role',
                'admin' => 'Administratorzy',
                'moderator' => 'Moderatorzy',
                'user' => 'Użytkownicy'
            ])
            ->setCondition(['users.role' => '%VALUE%']);
        
        $table->addFilter($roleFilter);
        
        // Akcje masowe
        $table->addAction('Aktywuj zaznaczone', 'javascript:performMassAction("activate")', 'btn btn-success mass-action-btn');
        $table->addAction('Dezaktywuj zaznaczone', 'javascript:performMassAction("deactivate")', 'btn btn-warning mass-action-btn');
        $table->addAction('Usuń zaznaczone', 'javascript:performMassAction("delete")', 'btn btn-danger mass-action-btn');
        $table->addAction('Export zaznaczonych', 'javascript:performMassAction("export")', 'btn btn-info mass-action-btn');
        
        $this->view->assign('userTable', $table->render());
        $this->view->render('users/advanced-ajax');
    }
    
    // Endpoint dla akcji masowych
    public function massAction()
    {
        if (!$this->request->isPost()) {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $action = $this->request->getPost('action');
        $userIds = $this->request->getPost('user_ids', []);
        
        if (empty($userIds) || !is_array($userIds)) {
            http_response_code(400);
            echo json_encode(['error' => 'No users selected']);
            return;
        }
        
        $userModel = $this->loadModel('User');
        
        try {
            $affectedRows = 0;
            
            switch ($action) {
                case 'activate':
                    foreach ($userIds as $userId) {
                        $userModel->setId($userId)->update(['status' => 'active']);
                        $affectedRows++;
                    }
                    echo json_encode([
                        'success' => true, 
                        'message' => "Aktywowano {$affectedRows} użytkowników"
                    ]);
                    break;
                    
                case 'deactivate':
                    foreach ($userIds as $userId) {
                        $userModel->setId($userId)->update(['status' => 'inactive']);
                        $affectedRows++;
                    }
                    echo json_encode([
                        'success' => true, 
                        'message' => "Dezaktywowano {$affectedRows} użytkowników"
                    ]);
                    break;
                    
                case 'delete':
                    foreach ($userIds as $userId) {
                        $userModel->setId($userId)->delete();
                        $affectedRows++;
                    }
                    echo json_encode([
                        'success' => true, 
                        'message' => "Usunięto {$affectedRows} użytkowników"
                    ]);
                    break;
                    
                case 'export':
                    $this->exportUsers($userIds);
                    return; // Nie zwracaj JSON dla eksportu
                    
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Unknown action']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    
    private function exportUsers(array $userIds)
    {
        $userModel = $this->loadModel('User');
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Nagłówki CSV
        fputcsv($output, ['ID', 'Nazwa', 'E-mail', 'Rola', 'Status', 'Data rejestracji']);
        
        // Dane użytkowników
        foreach ($userIds as $userId) {
            $user = $userModel->find(['users.id' => $userId]);
            if ($user) {
                fputcsv($output, [
                    $user['users']['id'],
                    $user['users']['name'],
                    $user['users']['email'],
                    $user['users']['role'],
                    $user['users']['status'],
                    $user['users']['created_at']
                ]);
            }
        }
        
        fclose($output);
    }
}
```

### Rozszerzony JavaScript dla akcji masowych

```javascript
// Dodaj do widoku advanced-ajax.phtml

// Funkcje dla akcji masowych
function performMassAction(action) {
    const checkedBoxes = document.querySelectorAll('.ajax-action-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        showToast('Nie zaznaczono żadnych użytkowników', 'warning');
        return;
    }
    
    const userIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    // Potwierdzenie dla destrukcyjnych akcji
    if (action === 'delete') {
        if (!confirm(`Czy na pewno chcesz usunąć ${userIds.length} użytkowników?`)) {
            return;
        }
    }
    
    // Wyłącz przyciski akcji masowych
    toggleMassActionButtons(false);
    
    if (action === 'export') {
        exportSelectedUsers(userIds);
        toggleMassActionButtons(true);
        return;
    }
    
    fetch('/users/mass-action', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `action=${action}&user_ids=${userIds.join('&user_ids=')}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            
            // Odśwież tabelę
            setTimeout(() => {
                refreshTable();
            }, 1500);
        } else {
            showToast(data.error || 'Wystąpił błąd', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Wystąpił błąd połączenia', 'error');
    })
    .finally(() => {
        toggleMassActionButtons(true);
    });
}

function exportSelectedUsers(userIds) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/users/mass-action';
    form.style.display = 'none';
    
    // Dodaj akcję
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'export';
    form.appendChild(actionInput);
    
    // Dodaj ID użytkowników
    userIds.forEach(id => {
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'user_ids[]';
        idInput.value = id;
        form.appendChild(idInput);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    showToast(`Eksportowanie ${userIds.length} użytkowników...`, 'info');
}

function toggleMassActionButtons(enabled) {
    const buttons = document.querySelectorAll('.mass-action-btn');
    buttons.forEach(button => {
        button.disabled = !enabled;
    });
}

// Obsługa zaznaczania wszystkich checkboxów
document.addEventListener('change', function(e) {
    if (e.target.id === 'select-all-checkbox') {
        const checkboxes = document.querySelectorAll('.ajax-action-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = e.target.checked;
        });
        updateMassActionButtonsState();
    } else if (e.target.classList.contains('ajax-action-checkbox')) {
        updateMassActionButtonsState();
    }
});

function updateMassActionButtonsState() {
    const checkedBoxes = document.querySelectorAll('.ajax-action-checkbox:checked');
    const massActionButtons = document.querySelectorAll('.mass-action-btn');
    
    const hasSelected = checkedBoxes.length > 0;
    
    massActionButtons.forEach(button => {
        button.style.opacity = hasSelected ? '1' : '0.5';
        button.style.pointerEvents = hasSelected ? 'auto' : 'none';
    });
    
    // Zaktualizuj licznik zaznaczonych
    const counter = document.getElementById('selected-counter');
    if (counter) {
        counter.textContent = `Zaznaczono: ${checkedBoxes.length}`;
    }
}
```

## Wskazówki dla tabel AJAX

1. **Klucz AJAX** - zawsze ustaw unikalny klucz (np. session_id())
2. **Tryb AJAX** - wywołaj `setAjax(true)` przed renderowaniem
3. **Baza danych** - tryb AJAX wymaga włączonej bazy danych
4. **JavaScript** - dołącz plik `table.js` do widoku
5. **Akcje masowe** - używaj `setAjaxAction(true)` dla checkboxów
6. **Obsługa błędów** - zawsze obsługuj błędy AJAX
7. **Powiadomienia** - używaj toast notifications dla UX
8. **Bezpieczeństwo** - waliduj wszystkie dane POST

## Zobacz także

- [Podstawowa tabela](podstawowa-tabela.md)
- [Tabela z filtrami](tabela-z-filtrami.md)
- [Klasa Table](../klasy/table.md)
- [Klasa Filter](../klasy/filter.md)