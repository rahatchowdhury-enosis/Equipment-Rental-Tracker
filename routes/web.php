<?php

use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('equipment', EquipmentController::class);
    Route::post('/equipment/{equipment}/duplicate', [EquipmentController::class, 'duplicate'])->name('equipment.duplicate');

    Route::resource('staff', StaffController::class);

    Route::get('rentals', [RentalController::class, 'index'])->name('rentals.index');
    Route::get('rentals/create', [RentalController::class, 'create'])->name('rentals.create');
    Route::post('rentals', [RentalController::class, 'store'])->name('rentals.store');
    Route::post('rentals/{rental}/return', [RentalController::class, 'returnRental'])->name('rentals.return');

    Route::get('reports/overdue', [ReportController::class, 'overdue'])->name('reports.overdue');
});

require __DIR__.'/auth.php';
