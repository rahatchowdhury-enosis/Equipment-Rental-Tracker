<?php

namespace App\Http\Controllers;

use App\Enums\Condition;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\Staff;
use App\Services\RentalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RentalController extends Controller
{
    public function __construct(private RentalService $rentalService) {}

    public function index(): View
    {
        return view('rentals.index', [
            'rentals' => Rental::with(['equipment', 'staff'])->latest('checked_out_at')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('rentals.create', [
            'equipment' => Equipment::available()->get(),
            'staff' => Staff::latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'staff_id' => 'required|exists:staff,id',
        ]);

        try {
            $this->rentalService->checkout(
                Equipment::findOrFail($data['equipment_id']),
                Staff::findOrFail($data['staff_id']),
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['rental' => $e->getMessage()]);
        }

        return redirect()->route('rentals.index')->with('status', 'Checked out.');
    }

    public function returnRental(Request $request, Rental $rental): RedirectResponse
    {
        $data = $request->validate([
            'condition' => ['required', Rule::enum(Condition::class)],
        ]);

        try {
            $rental = $this->rentalService->returnRental($rental, Condition::from($data['condition']));
        } catch (\DomainException $e) {
            return back()->withErrors(['rental' => $e->getMessage()]);
        }

        $lateFeeCents = $this->rentalService->calculateLateFee($rental);
        $status = $lateFeeCents > 0
            ? sprintf('Returned. Late fee: %s.', format_late_fee($lateFeeCents))
            : 'Returned.';

        return redirect()->route('rentals.index')->with('status', $status);
    }
}
