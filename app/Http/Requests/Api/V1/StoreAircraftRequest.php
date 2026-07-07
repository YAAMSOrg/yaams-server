<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Aircraft;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAircraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('add aircraft')
            && $this->user()->isMemberOf($this->route('airline'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'registration' => 'required|max:9|regex:/^[A-Z0-9]{1,2}-?[A-Z0-9]{3,5}$/i',
            'manufacturer' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'engine_type' => 'nullable|string|max:100',
            'satcom' => 'boolean',
            'winglets' => 'boolean',
            'selcal' => 'nullable|string|max:5|regex:/^[A-Z]{2}-?[A-Z]{2}$/i',
            'hex_code' => 'nullable|string|size:6|regex:/^[a-fA-F0-9]{6}$/i',
            'msn' => 'nullable|digits_between:1,6',
            'mtow' => 'nullable|integer|min:0|max:1000000',
            'mzfw' => 'nullable|integer|min:0|max:1000000',
            'mlw' => 'nullable|integer|min:0|max:1000000',
            'remarks' => 'nullable|string|max:1000',
            'current_loc' => 'required|max:4|exists:airports,icao_code',
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['registration', 'selcal', 'hex_code', 'current_loc'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => strtoupper($this->input($field))]);
            }
        }
    }

    /**
     * @return array<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $duplicate = Aircraft::query()
                    ->where('status', Aircraft::STATUS_ACTIVE)
                    ->where('registration', $this->input('registration'))
                    ->where('used_by', $this->route('airline')->id)
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add('registration', 'An active aircraft with this tail number already exist in this airline. Please set the aircraft inactive or choose another tail number.');
                }
            },
        ];
    }
}
