# Project Requirements — Equipment Rental Tracker (Laravel Demo)

## 1. Purpose

Small demo app to practice PHP Essentials Part 1 + Part 2 topics (Intro through PDO) in one coherent codebase. App: an **Equipment Rental Tracker** — staff check out tools/gear (cameras, drills, AV kit), track due-back dates, condition, and late fees. Built on **Laravel** so the app also shows how each raw-PHP concept maps onto framework conventions.

## 2. Tech Stack

- PHP 8.2+
- **Laravel 11** (routing, Eloquent ORM, Blade views, validation, auth scaffolding via Breeze — no API/SPA needed)
- MySQL/MariaDB (Eloquent for CRUD; one deliberate raw **PDO** query for the PDO topic — see §4)
- Composer (Laravel's own PSR-4 autoloading, `App\` → `/app`)
- VS Code + Xdebug3

## 3. Folder Structure (Laravel default, relevant parts only)

```
/app
  /Models              Equipment, Staff, Rental
  /Http/Controllers    EquipmentController, StaffController, RentalController
  /Http/Requests       StoreEquipmentRequest, StoreStaffRequest (form validation)
  /Enums               EquipmentStatus, RentalStatus, Role, Condition
  /Traits              Loggable
  /Attributes          Validate\MaxLength (custom, demo-only — see §4)
  /Exceptions          EquipmentNotAvailableException
  /Services            RentalService (checkout/return business logic, late fee calc)
/database
  /migrations          equipment, staff, rentals tables
  /factories, /seeders demo data
/resources/views        Blade templates (equipment, staff, rentals, auth)
/routes/web.php
```

## 4. Feature List Mapped to Topics

Laravel handles some topics natively (noted as "via framework"); a few need a **deliberate standalone example** since the framework normally hides them — those are flagged so the raw concept still gets practiced, not just delegated to a package.

### Part 1 — Core PHP

| Feature | Topic | Note |
|---|---|---|
| Rental due-date math, "days overdue" via Carbon (`DateTime` under the hood) | Dates & Times | via framework (Carbon) |
| Late fee calc (rate × days), status filters in controllers | Operators, Control Structures | plain PHP inside controllers/services |
| `array_filter`/`array_map` over collections before returning to view (in addition to Eloquent Collection methods) | Arrays | do at least one raw-array pass, not only `Collection::` helpers |
| `RentalService` methods, small helpers in `app/helpers.php` | Functions | plain functions, framework-agnostic |
| Typed properties on Models/DTOs, `declare(strict_types=1)` in Services | Variables & Data Types | |
| `.vscode/launch.json` + Xdebug config in repo | IDE/Xdebug | |

### Part 2 — OOP & Web

| Feature | Topic | Note |
|---|---|---|
| Eloquent `Equipment`, `Staff`, `Rental` models with relationships | Classes & Objects, OOP Intro | |
| `App\Models`, `App\Services`, etc. | Namespaces | via framework |
| PSR-12 throughout (Laravel Pint) | Code Style | |
| Composer `App\` → `/app` PSR-4 | Autoloading | via framework, already configured — confirm understanding, don't re-set-up |
| `Equipment::TABLE`, `Rental::MAX_EXTENSIONS` consts on models | Class Constants | |
| Custom `__toString()` on `Equipment`; note Eloquent's own `__get`/`__set` magic for attribute access | Magic Methods | write one custom one, then read Eloquent source for the built-in ones |
| `Equipment::available()` static scope, note Eloquent statics are LSB-driven (`Model::query()` uses `static::`) | Static Properties/Methods, Late Static Binding | mostly observed via framework; write one custom static factory on a non-Eloquent DTO class to actually practice it |
| `RentableInterface` implemented by `Equipment`, abstract `BaseService` | Principles of OOP | |
| `EquipmentStatus`, `RentalStatus`, `Role`, `Condition` as native PHP enums, cast on Eloquent models (`casts()` method) | Enums | |
| `Loggable` trait (custom, used by Services), note Laravel's own traits (`HasFactory`, etc.) on Models | Traits | |
| Custom PHP attribute (e.g. `#[MaxLength(120)]`) read via Reflection in a standalone validator class — **not** Laravel's routing (Laravel 11 route attributes are optional/rare); keep this example separate from framework code | Attributes | standalone example needed — framework doesn't force this one |
| Anonymous class for a quick "null object" `GuestStaff` in a Blade/controller edge case | Anonymous Classes | |
| `clone` on an `Equipment` model instance to duplicate a catalog entry (e.g. adding a second identical drill) | Working with Objects | |
| Export rental history to JSON (`toJson()`/`json_encode`); one manual `serialize()`/`unserialize()` demo with `__sleep`/`__wakeup` on a plain (non-Eloquent) class | Serialization | Eloquent's `toJson` covers JSON side; add the manual serialize demo separately |
| `EquipmentNotAvailableException`, Laravel's exception handler (`app/Exceptions`) rendering it as a flash message | Exception Handling | |
| Note `Request $request` object wraps `$_GET`/`$_POST`/`$_SERVER`; log/dump raw superglobals once in a controller to see what Laravel is wrapping | Superglobals & Basic Routing | framework abstracts this — do one raw dump to see it |
| `StoreEquipmentRequest`/`StoreStaffRequest` form request validation, Blade forms | Forms | |
| Laravel session-based auth (Breeze) for Admin/Staff roles; "remember me" cookie (Breeze default) | Sessions & Cookies | via framework |
| Equipment photo upload via `Storage::disk('public')` | File Uploading | via framework |
| Laravel's MVC (routes → controller → model → Blade view) | MVC Pattern | via framework — compare mentally to hand-rolled router from earlier plan |
| Eloquent for all CRUD; **one raw PDO query** (e.g. `DB::connection()->getPdo()->prepare(...)` for the "overdue equipment report") to explicitly touch PDO | PDO | standalone example needed — Eloquent hides PDO otherwise |

## 5. Core Domain Rules

- `Equipment`: id, name, category, serial_no, photo, condition (enum), status (enum), timestamps.
- `Staff`: id, name, email, role (enum), timestamps.
- `Rental`: equipment_id, staff_id, checked_out_at, due_at, returned_at, status (enum).
- Checkout allowed only if `EquipmentStatus::Available`; sets `CheckedOut`, due date = +7 days.
- Return sets `Rental::Returned`, equipment back to `Available`, sets `Condition` on return (Good/Damaged/Lost), late fee if overdue (5/day).
- Max 3 active rentals per staff member.

## 6. Routes (minimum, `routes/web.php`)

- Breeze auth routes (login/register/logout) — generated, not hand-written.
- `resource` routes: `equipment`, `staff`.
- `POST /rentals`, `POST /rentals/{rental}/return`.
- `GET /reports/overdue` (raw PDO query + serialize demo).

## 7. Database

Migrations for `equipment`, `staff`, `rentals` per §5. Factories/seeders for demo data (`php artisan db:seed`).

## 8. Out of Scope

- No API/SPA (Blade only).
- No automated test suite required (manual browser testing fine), though Laravel makes Pest/PHPUnit trivial to add later.
- No CSS framework beyond Breeze's default (Tailwind) — styling not graded.

## 9. Suggested Build Order

1. `laravel new`, install Breeze (session auth), run migrations.
2. Models + migrations + enums (`Equipment`, `Staff`, `Rental`).
3. Eloquent relationships, factories/seeders.
4. Equipment CRUD (Form Requests, Blade views, photo upload).
5. Staff CRUD.
6. `RentalService`: checkout/return, late fee calc, exceptions, max-3-rentals rule.
7. Standalone demo files (outside normal app flow, e.g. `app/Playground/`): custom Attribute + Reflection reader, manual `serialize()`/`__sleep`/`__wakeup`, anonymous class, raw PDO report query, raw superglobal dump.
8. Custom trait (`Loggable`) wired into `RentalService`.
9. Pint formatting pass, README with setup steps.
