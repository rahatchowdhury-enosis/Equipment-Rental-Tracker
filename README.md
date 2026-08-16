# Equipment Rental Tracker

Laravel demo app for practicing PHP Essentials Part 1 + Part 2 (Intro through PDO). Staff check out equipment (cameras, drills, AV kit), track due-back dates, condition, and late fees.

## Prerequisites

- PHP 8.2+
- Composer
- PostgreSQL
- Node.js (for Breeze/Vite frontend assets)

## Setup

```bash
git clone <repo-url>
cd equipment-rental-tracker
composer install
npm install && npm run build
cp .env.example .env
```

Create the Postgres role + database if they don't already exist:

```sql
CREATE ROLE equipment_rental LOGIN PASSWORD 'your-password-here';
CREATE DATABASE equipment_rental_tracker OWNER equipment_rental;
```

Set `DB_USERNAME`/`DB_PASSWORD` in `.env` to match, then:

```bash
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
```

Serve the app:

```bash
composer run dev   # runs php artisan serve + queue:listen + pail + vite, concurrently
```

## Xdebug / VS Code

`.vscode/launch.json` ships a "Listen for Xdebug" configuration on port 9003. Enable Xdebug in your PHP install, set breakpoints, then run the launch config from VS Code's Run and Debug panel before hitting a route.

## PHP Essentials — topic map

| Topic | Where |
|---|---|
| Dates & Times | `app/Services/RentalService.php` (due-date/late-fee math via Carbon) |
| Operators, Control Structures | `app/Services/RentalService.php`, controllers |
| Arrays (raw `array_filter`/`array_map`) | `app/Http/Controllers/EquipmentController.php` |
| Functions | `app/helpers.php`, `app/Services/RentalService.php` |
| Variables & Data Types (typed properties, `strict_types`) | `app/Services/*`, `app/DTOs/EquipmentSummary.php` |
| IDE/Xdebug | `.vscode/launch.json` |
| Classes & Objects, OOP Intro | `app/Models/Equipment.php`, `Staff.php`, `Rental.php` |
| Namespaces | throughout `app/` (framework PSR-4) |
| Code Style (PSR-12) | enforced via `./vendor/bin/pint` |
| Autoloading | `composer.json` (`App\` → `/app`) |
| Class Constants | `app/Models/Equipment.php`, `app/Models/Rental.php` |
| Magic Methods | `app/Models/Equipment.php::__toString()` |
| Static Properties/Methods, Late Static Binding | `app/Models/Equipment.php::available()` (framework LSB); `app/DTOs/EquipmentSummary.php::create()` (custom static factory) |
| Principles of OOP (interfaces, abstract classes) | `app/Contracts/RentableInterface.php`, `app/Services/BaseService.php` |
| Enums | `app/Enums/EquipmentStatus.php`, `RentalStatus.php`, `Role.php`, `Condition.php` |
| Traits | `app/Traits/Loggable.php` (used by `RentalService`) |
| Attributes | `app/Attributes/Validate/MaxLength.php` + `app/Playground/AttributeValidatorDemo.php` (standalone Reflection reader) |
| Anonymous Classes | `app/Http/Controllers/StaffController.php` (`GuestStaff` null object) |
| Working with Objects (clone/duplicate) | `app/Models/Equipment.php::duplicateWithSerial()` (via `replicate()`) |
| Serialization | `app/Playground/SerializationDemo.php` + `app/Playground/RentalHistoryRecord.php` (manual `serialize()`/`__sleep`/`__wakeup`); Eloquent `toJson()` used elsewhere |
| Exception Handling | `app/Exceptions/EquipmentNotAvailableException.php`, `bootstrap/app.php` renderable handler |
| Superglobals & Basic Routing | `app/Http/Controllers/EquipmentController.php` (raw `$_GET`/`$_POST`/`$_SERVER` dump, local-only) |
| Forms | `app/Http/Requests/StoreEquipmentRequest.php`, `StoreStaffRequest.php` |
| Sessions & Cookies | Breeze session auth (`routes/auth.php`) |
| File Uploading | `app/Http/Controllers/EquipmentController.php` (`Storage::disk('public')`) |
| MVC Pattern | `routes/web.php` → controllers → models → `resources/views` |
| PDO | `app/Http/Controllers/ReportController.php::overdue()` (raw `DB::connection()->getPdo()` query) |

## Running the Playground demos

The standalone demos in `app/Playground/` sit outside the normal app flow — run them via `tinker`:

```bash
php artisan tinker
```

```php
// Attribute + Reflection validator
$dto = new App\Playground\DemoDto();
$dto->title = str_repeat('x', 200);
App\Playground\AttributeValidatorDemo::validate($dto);

// Manual serialize()/__sleep/__wakeup
App\Playground\SerializationDemo::run();
```

## Tests

```bash
php artisan test          # PHPUnit suite (SQLite in-memory)
./vendor/bin/pint --test  # confirm PSR-12 formatting, no diffs
```
