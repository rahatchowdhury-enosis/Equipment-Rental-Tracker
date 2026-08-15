<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Check Out Equipment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 font-medium text-sm text-red-600">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('rentals.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="equipment_id" value="Equipment" />
                        <select id="equipment_id" name="equipment_id" class="mt-1 block w-full rounded-md border-gray-300">
                            @foreach ($equipment as $item)
                                <option value="{{ $item->id }}">{{ $item }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('equipment_id')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="staff_id" value="Staff" />
                        <select id="staff_id" name="staff_id" class="mt-1 block w-full rounded-md border-gray-300">
                            @foreach ($staff as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('staff_id')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>{{ __('Check Out') }}</x-primary-button>
                        <a href="{{ route('rentals.index') }}" class="text-sm text-gray-600 underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
