<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Aircraft;
use App\Models\Airline;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFlightRequest extends FormRequest
{
    private ?Airline $airline = null;

    private ?Aircraft $aircraft = null;

    public function authorize(): bool
    {
        // An unknown airline id is a validation error (422 via the exists
        // rule), not an authorization failure.
        $airline = $this->airline();

        return $airline === null || $this->user()->isMemberOf($airline);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'airline_id' => 'required|exists:airlines,id',
            'flightnumber' => 'numeric|digits_between:1,4|required',
            'departure_icao' => 'alpha|max:4|required|exists:airports,icao_code',
            'arrival_icao' => 'alpha|max:4|required|exists:airports,icao_code',
            'aircraft_id' => 'numeric|required',
            'callsign' => [
                'required',
                'max:6',
                'regex:/^[0-9]{1,4}[A-Za-z]{0,2}$/',
            ],
            'crzalt' => 'numeric|max:50000|digits_between:1,5|required',
            'blockoff' => 'required|date_format:Y-m-d H:i:s',
            'blockon' => 'required|date_format:Y-m-d H:i:s',
            'burned_fuel' => 'numeric|required',
            'route' => 'required',
            'online_network_id' => 'required|exists:online_networks,id',
            'remarks' => 'nullable|regex:/^[\pL\s\d\.\,\-]+$/u',
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['departure_icao', 'arrival_icao'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => strtoupper($this->input($field))]);
            }
        }
    }

    /**
     * Checks that need the airline/aircraft models. They run only when the
     * field rules passed and report as 422 validation errors like the rest.
     *
     * @return array<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $aircraft = $this->aircraft();
                if ($aircraft === null) {
                    $validator->errors()->add('aircraft_id', 'This aircraft is not available or not owned by your airline.');

                    return;
                }

                // Location continuity: the flight must depart from where the airframe currently is
                if ($this->airline()->location_continuity
                    && $this->input('departure_icao') !== strtoupper((string) $aircraft->current_loc)) {
                    $validator->errors()->add(
                        'departure_icao',
                        'Location continuity is enabled: ' . $aircraft->registration
                            . ' is currently located at ' . ($aircraft->current_loc ?: 'an unknown location') . '.'
                    );
                }
            },
        ];
    }

    public function airline(): ?Airline
    {
        return $this->airline ??= Airline::find($this->input('airline_id'));
    }

    /**
     * The aircraft named in the request, but only if it is active and owned
     * by the target airline - null otherwise.
     */
    public function aircraft(): ?Aircraft
    {
        return $this->aircraft ??= Aircraft::query()
            ->where('id', $this->input('aircraft_id'))
            ->where('used_by', $this->airline()?->id)
            ->where('status', Aircraft::STATUS_ACTIVE)
            ->first();
    }
}
