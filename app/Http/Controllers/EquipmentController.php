<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentStatus;
use App\Enums\RentalStatus;
use App\Http\Requests\StoreEquipmentRequest;
use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->query('status') === 'available'
            ? Equipment::available()
            : Equipment::query();

        return view('equipment.index', [
            'equipment' => $query->latest()->get(),
            'statusFilter' => $request->query('status'),
        ]);
    }

    public function create(): View
    {
        return view('equipment.create');
    }

    public function store(StoreEquipmentRequest $request): RedirectResponse
    {
        Equipment::create([
            ...$request->validated(),
            'photo' => $request->hasFile('photo') ? $request->file('photo')->store('equipment', 'public') : null,
        ]);

        return redirect()->route('equipment.index')->with('status', 'equipment-created');
    }

    public function show(Equipment $equipment): View
    {
        return view('equipment.show', ['equipment' => $equipment]);
    }

    public function edit(Equipment $equipment): View
    {
        return view('equipment.edit', ['equipment' => $equipment]);
    }

    public function update(StoreEquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        $photo = $equipment->photo;

        if ($request->hasFile('photo')) {
            if ($photo) {
                Storage::disk('public')->delete($photo);
            }

            $photo = $request->file('photo')->store('equipment', 'public');
        }

        $equipment->update([...$request->validated(), 'photo' => $photo]);

        return redirect()->route('equipment.index')->with('status', 'equipment-updated');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        if ($equipment->rentals()->where('status', RentalStatus::Active)->exists()) {
            return redirect()->route('equipment.index')->with('error', 'Cannot delete equipment with an active rental.');
        }

        if ($equipment->photo) {
            Storage::disk('public')->delete($equipment->photo);
        }

        $equipment->delete();

        return redirect()->route('equipment.index')->with('status', 'equipment-deleted');
    }

    public function duplicate(Equipment $equipment): RedirectResponse
    {
        $copy = $equipment->duplicateWithSerial($equipment->serial_no.'-COPY-'.Str::random(4));
        $copy->update(['photo' => null, 'status' => EquipmentStatus::Available]);

        return redirect()->route('equipment.index')->with('status', 'equipment-duplicated');
    }
}
