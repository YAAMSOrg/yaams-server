<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Airline;
use Illuminate\Foundation\Http\FormRequest;

class StoreAirlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Super-Admin, or anyone when `allow_user_airline_creation` is on (AirlinePolicy).
        return $this->user()->can('create', Airline::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:50|unique:airlines', // Example Airline
            'prefix' => 'required|min:2|max:2|unique:airlines|uppercase', // EV
            'icao_callsign' => 'required|regex:/^[a-zA-Z]+$/u|min:3|max:3|unique:airlines|uppercase', // EVA
            'atc_callsign' => 'required|regex:/^[a-zA-Z]+$/u|max:25|unique:airlines', // EXAMPLE
            'hub' => 'required|string|size:4|exists:airports,icao_code', // EDDF
            'country' => 'required|string|size:2|alpha', // DE
        ];
    }

    protected function prepareForValidation(): void
    {
        // Stored uppercase, like the web founding form.
        $this->merge(collect(['hub', 'country'])
            ->filter(fn (string $key) => is_string($this->input($key)))
            ->mapWithKeys(fn (string $key) => [$key => strtoupper($this->input($key))])
            ->all());
    }
}
