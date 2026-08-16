<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $staff->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500">Email</dt>
                        <dd class="text-gray-900">{{ $staff->email }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500">Role</dt>
                        <dd class="text-gray-900">{{ $staff->role->label() }}</dd>
                    </div>
                </dl>

                <div class="mt-6 flex items-center gap-4">
                    <a href="{{ route('staff.edit', $staff) }}" class="text-sm text-indigo-600 underline">{{ __('Edit') }}</a>

                    <x-confirm-delete
                        name="confirm-staff-deletion"
                        :action="route('staff.destroy', $staff)"
                        message="{{ __('Delete this staff member?') }}"
                    />

                    <a href="{{ route('staff.index') }}" class="text-sm text-gray-600 underline">{{ __('Back') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
