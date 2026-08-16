@props(['action', 'name', 'message' => 'Are you sure?'])

<x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', '{{ $name }}')">
    {{ __('Delete') }}
</x-danger-button>

<x-modal :name="$name" maxWidth="md" focusable>
    <form method="POST" action="{{ $action }}" class="p-6">
        @csrf
        @method('DELETE')

        <h2 class="text-lg font-medium text-gray-900">{{ $message }}</h2>

        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('No') }}
            </x-secondary-button>

            <x-danger-button class="ms-3">
                {{ __('Yes') }}
            </x-danger-button>
        </div>
    </form>
</x-modal>
