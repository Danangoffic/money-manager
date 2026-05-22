# Money Manager

Aplikasi manajemen keuangan rumah tangga berbasis web dengan fitur multi-user household, budgeting, goals, dan laporan keuangan.

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP 8.1+, Laravel 10 |
| Frontend | React 18, Inertia.js |
| Styling | Tailwind CSS 3 |
| Auth | Laravel Breeze + Sanctum + Google OAuth |
| Database | SQLite (dev) / MySQL / PostgreSQL |
| Bundler | Vite 5 |
| Charts | Recharts |

## Fitur Utama

- **Multi-Household** — Semua data terisolasi per household. User bisa diundang sebagai admin/member.
- **Manajemen Akun** — CRUD akun keuangan (bank, cash, e-wallet, credit card).
- **Transaksi** — Income, expense, transfer antar akun dengan soft delete & restore.
- **Kategori** — Custom kategori per household untuk income & expense.
- **Budget** — Set budget per kategori per bulan dengan alert saat mendekati/melebihi limit.
- **Goals** — Tujuan menabung dengan tracking progress.
- **Transaksi Berulang** — Recurring transactions (daily/weekly/monthly/yearly) diproses otomatis.
- **Dashboard** — Statistik, chart income vs expense, top categories, net worth.
- **Laporan** — Expense by category, income vs expense, cash flow.
- **Activity Log** — Audit trail semua perubahan data.
- **Export CSV** — Export data transaksi.
- **REST API** — JSON API v1 dengan Sanctum token auth.
- **Toast Notifications** — Feedback UI setelah setiap aksi.

## Instalasi

```bash
# Clone repository
git clone https://github.com/Danangoffic/money-manager.git
cd money-manager

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database (gunakan SQLite untuk development)
touch database/database.sqlite
php artisan migrate

# Build frontend
npm run build

# Jalankan server
php artisan serve
```

## Development

```bash
# Jalankan Vite dev server
npm run dev

# Jalankan Laravel server
php artisan serve

# Jalankan tests
php artisan test

# Format code
./vendor/bin/pint
```

## Struktur Aplikasi

```
app/
├── Console/Commands/       # Artisan commands (ProcessRecurringTransactions)
├── Http/
│   ├── Controllers/        # Web controllers (Inertia)
│   │   └── Api/V1/        # REST API controllers
│   ├── Middleware/         # Custom middleware
│   ├── Requests/           # Form request validation
│   └── Resources/          # API JSON resources
├── Models/                 # Eloquent models
├── Repositories/           # Repository pattern (interface + implementation)
├── Providers/              # Service providers (DI bindings)
└── Services/               # Business logic layer

resources/js/
├── Components/             # Reusable React components
│   └── Dashboard/          # Dashboard chart components
├── Layouts/                # App layouts (Authenticated, Guest)
└── Pages/                  # Inertia page components
    ├── Accounts/
    ├── ActivityLogs/
    ├── Auth/
    ├── Budgets/
    ├── Categories/
    ├── Dashboard.jsx
    ├── Goals/
    ├── Household/
    ├── RecurringTransactions/
    ├── Reports/
    └── Transactions/
```

## REST API

Base URL: `/api/v1`

### Authentication

```
POST   /api/v1/login          # Login, returns Bearer token
POST   /api/v1/register       # Register new user
POST   /api/v1/logout         # Logout (requires token)
GET    /api/v1/user           # Get current user
```

### Endpoints (requires Bearer token)

```
GET|POST           /api/v1/accounts
GET|PUT|DELETE     /api/v1/accounts/{id}

GET|POST           /api/v1/categories
GET|PUT|DELETE     /api/v1/categories/{id}

GET|POST           /api/v1/transactions
GET|PUT|DELETE     /api/v1/transactions/{id}

GET|POST|DELETE    /api/v1/budgets

GET|POST           /api/v1/goals
GET|PUT|DELETE     /api/v1/goals/{id}
PATCH              /api/v1/goals/{id}/progress
```

### Contoh Request

```bash
# Login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Create transaction (gunakan token dari login)
curl -X POST http://localhost:8000/api/v1/transactions \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"account_id":1,"category_id":1,"type":"expense","amount":50000,"date":"2025-01-15"}'
```

## Testing

```bash
# Run semua test
php artisan test

# Run test suite tertentu
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run test file spesifik
php artisan test tests/Feature/BudgetTest.php
php artisan test tests/Feature/Api/AuthTest.php
```

### Test Coverage

| Area | Status |
|------|--------|
| Account CRUD | ✅ |
| Transaction CRUD + Balance | ✅ |
| Soft Delete & Restore | ✅ |
| Budget CRUD & Alerts | ✅ |
| Goals CRUD & Progress | ✅ |
| Categories CRUD | ✅ |
| Recurring Transactions | ✅ |
| Reports | ✅ |
| Activity Log | ✅ |
| Household Management | ✅ |
| Authentication | ✅ |
| Profile Management | ✅ |
| API Auth (Sanctum) | ✅ |
| API Transactions | ✅ |

## Architecture Decisions

- **Repository Pattern** — Abstraksi database queries melalui interface untuk testability.
- **Service Layer** — Business logic dipisahkan dari controllers.
- **Soft Deletes** — Transaksi yang dihapus bisa dipulihkan.
- **Integer Currency** — Semua amount disimpan sebagai integer (satuan terkecil) untuk menghindari floating-point errors.
- **Household Scoping** — Semua data di-scope per household untuk multi-tenant support.
- **Inertia.js** — SPA experience tanpa perlu API terpisah untuk frontend.

## Deploy (Vercel)

Project ini dikonfigurasi untuk deploy di Vercel menggunakan `vercel-php` runtime. Lihat `vercel.json` untuk konfigurasi.

## License

MIT
