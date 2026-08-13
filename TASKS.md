# Task Breakdown — Equipment Rental Tracker

Checklist form of REQUIREMENTS.md §9. Work top to bottom — each phase depends on prior.

## Phase 0 — Project Setup

- [ ] `composer create-project laravel/laravel equipment-rental-tracker`
- [ ] `composer require laravel/breeze --dev` → `php artisan breeze:install` (Blade stack)
- [ ] Configure `.env` (DB name/user/pass), `php artisan migrate` (default Breeze tables)
- [ ] Commit `.vscode/launch.json` for Xdebug3
- [ ] `php artisan storage:link` (for photo uploads later)

## Phase 1 — Enums

- [ ] `app/Enums/EquipmentStatus.php` — `Available`, `CheckedOut`, `Retired`
- [ ] `app/Enums/RentalStatus.php` — `Active`, `Returned`, `Overdue`
- [ ] `app/Enums/Condition.php` — `Good`, `Damaged`, `Lost`
- [ ] `app/Enums/Role.php` — `Admin`, `Staff`

## Phase 2 — Migrations & Models

- [ ] Migration: `equipment` (name, category, serial_no, photo, condition, status, timestamps)
- [ ] Migration: `staff` (or extend Breeze's `users` table with `role` column — decide one)
- [ ] Migration: `rentals` (equipment_id FK, staff_id FK, checked_out_at, due_at, returned_at, status)
- [ ] `Equipment` model: casts for `status`/`condition` enums, `TABLE` const, relationships (`hasMany(Rental)`)
- [ ] `Staff`/`User` model: casts for `role` enum, relationships (`hasMany(Rental)`)
- [ ] `Rental` model: casts for `status` enum, `MAX_EXTENSIONS` const, `belongsTo(Equipment)`, `belongsTo(Staff)`
- [ ] Custom `__toString()` on `Equipment`
- [ ] `RentableInterface` + `Equipment implements RentableInterface`
- [ ] Factories + seeders for all three, `php artisan db:seed` produces demo data

## Phase 3 — Equipment CRUD

- [ ] `StoreEquipmentRequest` (validation: name required, category required, serial_no unique)
- [ ] `EquipmentController` (index/create/store/edit/update/destroy)
- [ ] Blade views: list (with filter by status), create/edit form, show
- [ ] Photo upload on create/edit via `Storage::disk('public')`
- [ ] `Equipment::available()` static scope method
- [ ] `clone` demo: "duplicate equipment" button/action on show page

## Phase 4 — Staff CRUD

- [ ] `StoreStaffRequest` validation
- [ ] `StaffController` (index/create/store/edit/update/destroy) — admin-only (role check)
- [ ] Blade views: list, create/edit form

## Phase 5 — Rental Lifecycle

- [ ] `EquipmentNotAvailableException` (extends `\Exception`)
- [ ] `app/Traits/Loggable.php` — custom trait (log action + timestamp to a `logs` table or `storage/logs`)
- [ ] `RentalService`:
  - [ ] `checkout(Equipment $equipment, Staff $staff): Rental` — checks `EquipmentStatus::Available`, checks max-3-active-rentals rule, throws `EquipmentNotAvailableException` on failure, sets due date +7 days
  - [ ] `return(Rental $rental, Condition $condition): Rental` — sets `Returned`, updates equipment status/condition, calculates late fee (5/day if overdue)
  - [ ] uses `Loggable` trait
- [ ] `RentalController`: `store` (checkout), `update`/custom route (return) — try/catch around service calls, flash error message via Laravel's exception handling
- [ ] Blade views: rentals list (active/overdue highlighted), checkout form, return form (condition select)
- [ ] `declare(strict_types=1)` in `RentalService`

## Phase 6 — Standalone Concept Demos (`app/Playground/`)

Not part of normal app flow — small isolated scripts/classes proving each concept, since Laravel abstracts them elsewhere.

- [ ] `app/Playground/Attributes/MaxLength.php` — custom PHP attribute class
- [ ] `app/Playground/Attributes/AttributeValidator.php` — reads `#[MaxLength(120)]` off a DTO's properties via Reflection, returns pass/fail
- [ ] `app/Playground/Serialization/RentalRecord.php` — plain (non-Eloquent) class with `__sleep`/`__wakeup`, demo `serialize()`/`unserialize()` round-trip
- [ ] `app/Playground/AnonymousClass/GuestStaff` — anonymous class used as a null-object fallback in one controller edge case (e.g. rental with no assigned staff)
- [ ] `app/Playground/RawPdo/OverdueReport.php` — `DB::connection()->getPdo()->prepare(...)` query for overdue equipment, exposed at `GET /reports/overdue`
- [ ] One raw superglobal dump (`dd($_SERVER, $_GET)`) left as a comment/commented route in a controller, with a one-line note on what `Request $request` normally wraps
- [ ] Static factory + LSB demo: plain (non-Eloquent) `Report` base class with `public static function make(): static` overridden behavior in a child class

## Phase 7 — Polish

- [ ] `vendor/bin/pint` formatting pass (PSR-12)
- [ ] Role-based route middleware (Admin vs Staff access to Staff CRUD)
- [ ] Error/404 Blade views
- [ ] README: setup steps (clone, composer install, .env, migrate, seed, serve)
- [ ] Final pass: confirm every row in REQUIREMENTS.md §4 table has a corresponding checked-off task here

## Stretch (optional, only if time remains)

- [ ] `GET /reports/overdue` export as downloadable JSON file
- [ ] Simple dashboard: counts by `EquipmentStatus`, overdue count
- [ ] Pest/PHPUnit: one feature test for checkout flow, one unit test for `RentalService::return()` fee calc
