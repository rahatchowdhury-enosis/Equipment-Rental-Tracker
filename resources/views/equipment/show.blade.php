<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $equipment->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if ($equipment->photo)
                    <img src="{{ asset('storage/'.$equipment->photo) }}" alt="{{ $equipment->name }}" class="w-full max-h-80 object-cover rounded-md mb-6">
                @else
                    <p class="text-sm text-gray-400 mb-6">No photo</p>
                @endif

                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500">Category</dt>
                        <dd class="text-gray-900">{{ $equipment->category }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Serial No</dt>
                        <dd class="text-gray-900">{{ $equipment->serial_no }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Condition</dt>
                        <dd class="text-gray-900">{{ $equipment->condition->label() }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Status</dt>
                        <dd class="text-gray-900">{{ $equipment->status->label() }}</dd>
                    </div>
                </dl>

                <div class="mt-6 flex items-center gap-4">
                    <a href="{{ route('equipment.edit', $equipment) }}" class="text-sm text-indigo-600 underline">{{ __('Edit') }}</a>

                    <form method="POST" action="{{ route('equipment.duplicate', $equipment) }}">
                        @csrf
                        <x-secondary-button type="submit">{{ __('Duplicate') }}</x-secondary-button>
                    </form>

                    <form method="POST" action="{{ route('equipment.destroy', $equipment) }}" onsubmit="return confirm('Delete this equipment?')">
                        @csrf
                        @method('DELETE')
                        <x-danger-button type="submit">{{ __('Delete') }}</x-danger-button>
                    </form>

                    <a href="{{ route('equipment.index') }}" class="text-sm text-gray-600 underline">{{ __('Back') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
