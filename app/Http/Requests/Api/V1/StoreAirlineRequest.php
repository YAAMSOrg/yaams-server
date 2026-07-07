<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreAirlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('add airlines');
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
        ];
    }
}
