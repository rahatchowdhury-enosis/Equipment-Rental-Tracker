<?php

namespace App\Http\Requests;

use App\Enums\Condition;
use App\Enums\EquipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:60'],
            'serial_no' => ['required', 'string', 'max:60', Rule::unique('equipment', 'serial_no')->ignore($this->route('equipment'))],
            'photo' => ['nullable', 'image', 'max:2048'],
            'condition' => ['required', Rule::enum(Condition::class)],
            'status' => ['required', Rule::enum(EquipmentStatus::class)],
        ];
    }
}
