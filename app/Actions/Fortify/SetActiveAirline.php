<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;

class SetActiveAirline
{
    /**
     * Befindet sich mitten in der Fortify Login-Pipeline.
     */
    public function handle(Request $request, $next)
    {
        // Fortify authentifiziert den User kurz vor diesem Schritt.
        // Wenn der Login erfolgreich war, greifen wir uns den User:
        if (auth()->check()) {
            $user = auth()->user();
            
            // Die erste Airline des Users suchen
            $firstAirlineFound = $user->airlines()->first();

            if ($firstAirlineFound) {
                $request->session()->put('activeairline', $firstAirlineFound);
            } else {
                // FIXME/TODO: Was tun, wenn der User in keiner Airline ist?
                // Für den Übergang loggen wir ihn entweder aus oder lassen das Feld leer.
                // $request->session()->put('activeairline', null);
            }
        }

        return $next($request);
    }
}
