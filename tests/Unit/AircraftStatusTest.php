<?php

namespace Tests\Unit;

use App\Models\Aircraft;
use Tests\TestCase;

/**
 * Aircraft lifecycle accessors derived from the `status` column - no database.
 */
class AircraftStatusTest extends TestCase
{
    public function test_active_status_is_reported_as_active(): void
    {
        $aircraft = new Aircraft(['status' => Aircraft::STATUS_ACTIVE]);

        $this->assertTrue($aircraft->active);
        $this->assertFalse($aircraft->isRetired());
    }

    public function test_inactive_status_is_not_active_and_not_retired(): void
    {
        $aircraft = new Aircraft(['status' => Aircraft::STATUS_INACTIVE]);

        $this->assertFalse($aircraft->active);
        $this->assertFalse($aircraft->isRetired());
    }

    public function test_retired_status_is_reported_as_retired(): void
    {
        $aircraft = new Aircraft(['status' => Aircraft::STATUS_RETIRED]);

        $this->assertTrue($aircraft->isRetired());
        $this->assertFalse($aircraft->active);
    }
}
