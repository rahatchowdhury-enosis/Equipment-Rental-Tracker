<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Overdue Equipment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Equipment</th>
                            <th class="px-6 py-3">Staff</th>
                            <th class="px-6 py-3">Due</th>
                            <th class="px-6 py-3">Days Overdue</th>
                            <th class="px-6 py-3">Late Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr class="border-t">
                                <td class="px-6 py-4">{{ $row['equipment_name'] }}</td>
                                <td class="px-6 py-4">{{ $row['staff_name'] }}</td>
                                <td class="px-6 py-4">{{ \Illuminate\Support\Carbon::parse($row['due_at'])->format('Y-m-d') }}</td>
                                @php($daysOverdue = days_between(\Illuminate\Support\Carbon::parse($row['due_at']), now()))
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">
                                        {{ $daysOverdue }} days overdue
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-red-700">
                                    {{ format_late_fee(max(0, $daysOverdue) * \App\Models\Rental::LATE_FEE_CENTS_PER_DAY) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-gray-400">No overdue equipment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
