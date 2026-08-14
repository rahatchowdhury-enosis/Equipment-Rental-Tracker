<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffRequest;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        return view('staff.index', ['staff' => Staff::latest()->get()]);
    }

    public function create(): View
    {
        return view('staff.create');
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        Staff::create($request->validated());

        return redirect()->route('staff.index')->with('status', 'staff-created');
    }

    public function show(Staff $staff): View
    {
        return view('staff.show', ['staff' => $staff]);
    }

    public function edit(Staff $staff): View
    {
        return view('staff.edit', ['staff' => $staff]);
    }

    public function update(StoreStaffRequest $request, Staff $staff): RedirectResponse
    {
        $staff->update($request->validated());

        return redirect()->route('staff.index')->with('status', 'staff-updated');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        if ($staff->activeRentals()->exists()) {
            return redirect()->route('staff.index')->with('error', 'Cannot delete staff with an active rental.');
        }

        $staff->delete();

        return redirect()->route('staff.index')->with('status', 'staff-deleted');
    }

    /**
     * Resolve current staff, or a null-object guest when the logged-in user has no linked staff row.
     */
    protected function currentStaffOrGuest(): Staff
    {
        $userId = auth()->id();
        $staff = $userId ? Staff::where('user_id', $userId)->first() : null;

        if ($staff) {
            return $staff;
        }

        return new class extends Staff
        {
            public function getNameAttribute(): string
            {
                return 'Guest';
            }

            public function activeRentals(): HasMany
            {
                return $this->rentals()->whereRaw('0 = 1');
            }
        };
    }
}
