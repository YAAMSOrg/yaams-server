<?php

namespace Tests\Unit;

use App\Support\MapBounds;
use PHPUnit\Framework\TestCase;

/**
 * MapBounds derives a Leaflet view (center + zoom) framing a set of points,
 * standing in for the Leaflet component's missing client-side fitToBounds.
 */
class MapBoundsTest extends TestCase
{
    public function test_no_points_returns_the_whole_world(): void
    {
        $view = MapBounds::fit([]);

        $this->assertSame(['lat' => 20.0, 'long' => 0.0], $view['center']);
        $this->assertSame(2, $view['zoom']);
    }

    public function test_single_point_centers_on_it_and_zooms_in(): void
    {
        $view = MapBounds::fit([['lat' => 50.0333, 'long' => 8.5706]]);

        $this->assertSame(['lat' => 50.0333, 'long' => 8.5706], $view['center']);
        $this->assertSame(5, $view['zoom']);
    }

    public function test_multiple_points_center_on_the_bounding_box_midpoint(): void
    {
        $view = MapBounds::fit([
            ['lat' => 40.0, 'long' => -10.0],
            ['lat' => 50.0, 'long' => 10.0],
        ]);

        $this->assertEqualsWithDelta(45.0, $view['center']['lat'], 0.0001);
        $this->assertEqualsWithDelta(0.0, $view['center']['long'], 0.0001);
    }

    public function test_zoom_is_clamped_to_the_supported_range(): void
    {
        // A globe-spanning spread stays zoomed out (>= 2).
        $wide = MapBounds::fit([
            ['lat' => -80.0, 'long' => -170.0],
            ['lat' => 80.0, 'long' => 170.0],
        ]);
        $this->assertGreaterThanOrEqual(2, $wide['zoom']);
        $this->assertLessThanOrEqual(10, $wide['zoom']);

        // Two airports a few degrees apart never exceed the max zoom.
        $tight = MapBounds::fit([
            ['lat' => 50.0333, 'long' => 8.5706],
            ['lat' => 51.4700, 'long' => -0.4543],
        ]);
        $this->assertLessThanOrEqual(10, $tight['zoom']);
        $this->assertGreaterThanOrEqual(2, $tight['zoom']);
    }
}
