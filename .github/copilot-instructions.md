# Copilot instructions (hospitalManagementSystem)

## Project shape (Laravel 12)
- This is a role-based Laravel app; the main entry routing is in `routes/web.php` with route groups per role/prefix: `admin.*`, `doctor.*`, `reception.*`, `patient.*`, `pharmacy.*`.
- Post-login routing uses the user’s role name string (`admin|doctor|patient|pharmacy|receptionist`) to redirect from `/dashboard` (see `routes/web.php`). Keep these role name strings consistent with DB data/seeders.

## Auth, roles, middleware
- Auth scaffolding is Laravel Breeze (see `routes/auth.php`). Protected areas typically use `['auth','verified']`.
- Role checks use custom middleware alias `role` → `App\Http\Middleware\CheckRole` (registered in `bootstrap/app.php`). Use it as `->middleware('role:admin')` or in groups (examples in `routes/web.php`).
- Locale is set on every web request by `App\Http\Middleware\SetLocale` (added to the web stack in `bootstrap/app.php`). It prioritizes `session('locale')`, otherwise `Settings::first()->language`.
- There’s a simple locale switch route `/lang/{locale}` limited to `ar|en` for quick testing (see `routes/web.php`).

## UI / frontend build
- Blade templates live in `resources/views/**`. Assets are built with Vite + `laravel-vite-plugin` (see `vite.config.js`). Inputs: `resources/css/app.css` and `resources/js/app.js`.
- Tailwind is configured in `tailwind.config.js` and includes a safelist of button color classes; prefer using existing Tailwind utility patterns rather than introducing new custom CSS.

## Queue + background work
- The default queue connection is `database` (see `config/queue.php`). Local dev commonly runs a listener alongside the server.

## Reporting & exports
- Admin reports use:
  - PDF export via `barryvdh/laravel-dompdf` in `App\Http\Controllers\Admin\ReportController`.
  - Excel export via `phpoffice/phpspreadsheet` using `App\Exports\ReportsExport`.
- `ReportController` generates locale-aware chart/table labels with Carbon’s `translatedFormat()`, but forces English for PDF (`isArabic=false`) because DomPDF RTL support is limited.

## Activity logging convention
- Some actions are logged via `App\Traits\LogsActivity`, which inserts directly into `activity_logs` using the DB facade. If you add admin actions that should be audited, follow the same pattern (`$this->logActivity('action_code', ['meta' => ...])`).

## Developer workflows (PowerShell)
- One-shot setup: `composer run setup` (installs deps, creates `.env`, generates key, migrates, installs/builds frontend).
- Dev (server + queue + Vite in one command): `composer run dev`.
- Tests: `composer run test` (clears config, then runs `php artisan test`).
- Manual DB backup (MySQL/MAMP path): run `backup_db_to_d_backups.bat`.

## Repo-specific pitfalls
- Namespaces/casing: some controllers are referenced as `App\Http\Controllers\Doctor\...` and others as `App\Http\Controllers\doctor\...` in `routes/web.php`. Be careful when creating/moving controllers—Windows is case-insensitive but Linux deployments are not.
