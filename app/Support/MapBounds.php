<?php

namespace App\Support;

/**
 * Computes a Leaflet map view (center + zoom) that frames a set of geographic
 * points. The larswiegers/laravel-maps Leaflet component has no client-side
 * `fitToBounds`, so we derive a sensible center and zoom on the server and feed
 * them to the component's `centerPoint` / `zoomLevel` props.
 *
 * Points are `['lat' => float, 'long' => float]` (the component's marker keys).
 */
class MapBounds
{
    /** Clamp range for the derived zoom level (Leaflet 0 = whole world). */
    private const MIN_ZOOM = 2;
    private const MAX_ZOOM = 10;

    /**
     * @param  array<int, array{lat: float, long: float}>  $points
     * @return array{center: array{lat: float, long: float}, zoom: int}
     */
    public static function fit(array $points): array
    {
        // No points: show the whole world.
        if (empty($points)) {
            return ['center' => ['lat' => 20.0, 'long' => 0.0], 'zoom' => self::MIN_ZOOM];
        }

        $lats = array_column($points, 'lat');
        $lngs = array_column($points, 'long');

        // Single point (or several stacked on one spot): center on it, zoom in.
        if (count($points) === 1) {
            return [
                'center' => ['lat' => (float) $lats[0], 'long' => (float) $lngs[0]],
                'zoom' => 5,
            ];
        }

        $minLat = min($lats);
        $maxLat = max($lats);
        $minLng = min($lngs);
        $maxLng = max($lngs);

        $center = [
            'lat' => ($minLat + $maxLat) / 2,
            'long' => ($minLng + $maxLng) / 2,
        ];

        // Zoom from the larger of the two spans so both fit; -1 for padding.
        $span = max($maxLat - $minLat, $maxLng - $minLng, 0.0001);
        $zoom = (int) floor(log(360 / $span, 2)) - 1;
        $zoom = max(self::MIN_ZOOM, min(self::MAX_ZOOM, $zoom));

        return ['center' => $center, 'zoom' => $zoom];
    }
}
