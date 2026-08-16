<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rentals') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 font-medium text-sm text-red-600">{{ $errors->first() }}</div>
            @endif

            <div class="mb-4 flex items-center justify-end">
                <a href="{{ route('rentals.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Check Out Equipment</a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Equipment</th>
                            <th class="px-6 py-3">Staff</th>
                            <th class="px-6 py-3">Due</th>
                            <th class="px-6 py-3">Late Fee</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rentals as $rental)
                            <tr class="border-t">
                                <td class="px-6 py-4">{{ $rental->equipment }}</td>
                                <td class="px-6 py-4">{{ $rental->staff->name }}</td>
                                <td class="px-6 py-4">
                                    {{ $rental->due_at->format('Y-m-d') }}
                                    @php($daysOverdue = days_between($rental->due_at, now()))
                                    @if ($rental->status === \App\Enums\RentalStatus::Active && $daysOverdue > 0)
                                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">
                                            {{ $daysOverdue }} days overdue
                                        </span>
                                    @endif
                                </td>
                                @php($lateFeeCents = $rental->lateFeeCents())
                                <td class="px-6 py-4 {{ $lateFeeCents > 0 ? 'font-semibold text-red-700' : 'text-gray-400' }}">
                                    {{ format_late_fee($lateFeeCents) }}
                                    @if ($lateFeeCents > 0 && $rental->status === \App\Enums\RentalStatus::Active)
                                        <span class="block text-xs font-normal text-gray-500">accruing</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $rental->status->label() }}</td>
                                <td class="px-6 py-4 text-right">
                                    @if ($rental->status === \App\Enums\RentalStatus::Active)
                                        <form method="POST" action="{{ route('rentals.return', $rental) }}" class="flex items-center justify-end gap-2">
                                            @csrf
                                            <select name="condition" class="rounded-md border-gray-300 text-xs">
                                                @foreach (\App\Enums\Condition::cases() as $condition)
                                                    <option value="{{ $condition->value }}">{{ $condition->label() }}</option>
                                                @endforeach
                                            </select>
                                            <x-primary-button>Return</x-primary-button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-gray-400">No rentals found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $rentals->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
