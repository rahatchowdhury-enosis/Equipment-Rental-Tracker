@props(['equipment' => null])

<div>
    <x-input-label for="name" value="Name" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $equipment?->name) }}" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="category" value="Category" />
    <x-text-input id="category" name="category" type="text" class="mt-1 block w-full" value="{{ old('category', $equipment?->category) }}" required />
    <x-input-error :messages="$errors->get('category')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="serial_no" value="Serial No" />
    <x-text-input id="serial_no" name="serial_no" type="text" class="mt-1 block w-full" value="{{ old('serial_no', $equipment?->serial_no) }}" required />
    <x-input-error :messages="$errors->get('serial_no')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="condition" value="Condition" />
    <select id="condition" name="condition" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        @foreach (\App\Enums\Condition::cases() as $condition)
            <option value="{{ $condition->value }}" @selected(old('condition', $equipment?->condition?->value) === $condition->value)>{{ $condition->label() }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('condition')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="status" value="Status" />
    <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        @foreach (\App\Enums\EquipmentStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(old('status', $equipment?->status?->value) === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('status')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="photo" value="Photo" />
    <input id="photo" name="photo" type="file" accept="image/*" class="mt-1 block w-full text-sm">
    <x-input-error :messages="$errors->get('photo')" class="mt-2" />
</div>
