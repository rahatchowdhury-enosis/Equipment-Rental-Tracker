<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Equipment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ [
                        'equipment-created' => 'Equipment created.',
                        'equipment-updated' => 'Equipment updated.',
                        'equipment-deleted' => 'Equipment deleted.',
                        'equipment-duplicated' => 'Equipment duplicated.',
                    ][session('status')] ?? session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 font-medium text-sm text-red-600">{{ session('error') }}</div>
            @endif

            <div class="mb-4 flex items-center justify-between">
                <div class="flex gap-4 text-sm">
                    <a href="{{ route('equipment.index') }}" class="{{ $statusFilter ? 'text-gray-600' : 'font-semibold text-gray-900' }}">All</a>
                    <a href="{{ route('equipment.index', ['status' => 'available']) }}" class="{{ $statusFilter === 'available' ? 'font-semibold text-gray-900' : 'text-gray-600' }}">Available</a>
                </div>
                <a href="{{ route('equipment.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Add Equipment</a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Category</th>
                            <th class="px-6 py-3">Serial No</th>
                            <th class="px-6 py-3">Condition</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($equipment as $item)
                            <tr class="border-t">
                                <td class="px-6 py-4">{{ $item->name }}</td>
                                <td class="px-6 py-4">{{ $item->category }}</td>
                                <td class="px-6 py-4">{{ $item->serial_no }}</td>
                                <td class="px-6 py-4">{{ $item->condition->label() }}</td>
                                <td class="px-6 py-4">{{ $item->status->label() }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('equipment.show', $item) }}" class="text-indigo-600 underline">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-gray-400">No equipment found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
