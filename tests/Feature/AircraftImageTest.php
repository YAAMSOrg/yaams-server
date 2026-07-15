<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\AircraftImage;
use App\Models\Airline;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * Aircraft screenshot gallery: manager-only upload/manage, member-only view,
 * server-side validation, WebP re-encoding, and primary-image invariants.
 */
class AircraftImageTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Storage::fake('local');
    }

    /**
     * @return array{0: Airline, 1: Aircraft, 2: User, 3: User}
     */
    private function airlineFixture(): array
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $manager = $this->memberOf($airline, 'Manager');
        $pilot = $this->memberOf($airline, 'Pilot');

        return [$airline, $aircraft, $manager, $pilot];
    }

    public function test_manager_can_upload_a_screenshot_and_first_is_primary(): void
    {
        [$airline, $aircraft, $manager] = $this->airlineFixture();

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->post(route('aircraft.images.store', $aircraft->id), [
                'screenshot' => UploadedFile::fake()->image('shot.png', 800, 600),
            ])
            ->assertRedirect(route('viewaircraft', $aircraft->id));

        $this->assertDatabaseCount('aircraft_images', 1);
        $image = AircraftImage::firstOrFail();
        $this->assertTrue($image->is_primary);
        $this->assertStringEndsWith('.webp', $image->path);
        Storage::disk('local')->assertExists($image->path);
    }

    public function test_second_upload_is_not_primary(): void
    {
        [$airline, $aircraft, $manager] = $this->airlineFixture();

        foreach (['a.png', 'b.png'] as $name) {
            $this->actingAs($manager)
                ->withSession(['activeairline' => $airline])
                ->post(route('aircraft.images.store', $aircraft->id), [
                    'screenshot' => UploadedFile::fake()->image($name, 800, 600),
                ]);
        }

        $this->assertSame(1, $aircraft->images()->where('is_primary', true)->count());
    }

    public function test_upload_rejects_non_image_file(): void
    {
        [$airline, $aircraft, $manager] = $this->airlineFixture();

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->post(route('aircraft.images.store', $aircraft->id), [
                'screenshot' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('screenshot');

        $this->assertDatabaseCount('aircraft_images', 0);
    }

    public function test_upload_rejects_oversized_dimensions(): void
    {
        [$airline, $aircraft, $manager] = $this->airlineFixture();
        Setting::set('aircraft_image_max_dimension', '1000');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->post(route('aircraft.images.store', $aircraft->id), [
                'screenshot' => UploadedFile::fake()->image('huge.png', 2000, 1500),
            ])
            ->assertSessionHasErrors('screenshot');

        $this->assertDatabaseCount('aircraft_images', 0);
    }

    public function test_upload_rejects_when_gallery_is_full(): void
    {
        [$airline, $aircraft, $manager] = $this->airlineFixture();
        Setting::set('aircraft_image_max_per_aircraft', '1');
        AircraftImage::factory()->primary()->create(['aircraft_id' => $aircraft->id]);

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->post(route('aircraft.images.store', $aircraft->id), [
                'screenshot' => UploadedFile::fake()->image('extra.png', 800, 600),
            ])
            ->assertSessionHasErrors('screenshot');

        $this->assertDatabaseCount('aircraft_images', 1);
    }

    public function test_pilot_cannot_upload(): void
    {
        [$airline, $aircraft, , $pilot] = $this->airlineFixture();

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->post(route('aircraft.images.store', $aircraft->id), [
                'screenshot' => UploadedFile::fake()->image('shot.png', 800, 600),
            ]);

        $this->assertDatabaseCount('aircraft_images', 0);
    }

    public function test_member_can_view_but_outsider_cannot(): void
    {
        [$airline, $aircraft, , $pilot] = $this->airlineFixture();
        $image = AircraftImage::factory()->primary()->create(['aircraft_id' => $aircraft->id]);
        Storage::disk('local')->put($image->path, 'fake-bytes');

        // Member of the owning airline (aircraft owned by active airline) -> 200.
        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('aircraft.images.show', [$aircraft->id, $image->id]))
            ->assertOk();

        // A user whose active airline does not own the aircraft -> 403.
        $otherAirline = Airline::factory()->create();
        $outsider = $this->memberOf($otherAirline, 'Pilot');
        $this->actingAs($outsider)
            ->withSession(['activeairline' => $otherAirline])
            ->get(route('aircraft.images.show', [$aircraft->id, $image->id]))
            ->assertForbidden();
    }

    public function test_set_primary_flips_the_flag(): void
    {
        [$airline, $aircraft, $manager] = $this->airlineFixture();
        $first = AircraftImage::factory()->primary()->create(['aircraft_id' => $aircraft->id]);
        $second = AircraftImage::factory()->create(['aircraft_id' => $aircraft->id]);

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->patch(route('aircraft.images.primary', [$aircraft->id, $second->id]))
            ->assertRedirect(route('viewaircraft', $aircraft->id));

        $this->assertFalse($first->fresh()->is_primary);
        $this->assertTrue($second->fresh()->is_primary);
    }

    public function test_deleting_primary_promotes_another_and_removes_file(): void
    {
        [$airline, $aircraft, $manager] = $this->airlineFixture();
        $primary = AircraftImage::factory()->primary()->create(['aircraft_id' => $aircraft->id]);
        $other = AircraftImage::factory()->create(['aircraft_id' => $aircraft->id]);
        Storage::disk('local')->put($primary->path, 'bytes');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->delete(route('aircraft.images.destroy', [$aircraft->id, $primary->id]))
            ->assertRedirect(route('viewaircraft', $aircraft->id));

        $this->assertDatabaseMissing('aircraft_images', ['id' => $primary->id]);
        Storage::disk('local')->assertMissing($primary->path);
        $this->assertTrue($other->fresh()->is_primary);
    }

    public function test_scoped_binding_rejects_image_from_another_aircraft(): void
    {
        [$airline, $aircraft, $manager] = $this->airlineFixture();
        $otherAircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $foreignImage = AircraftImage::factory()->create(['aircraft_id' => $otherAircraft->id]);

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->get(route('aircraft.images.show', [$aircraft->id, $foreignImage->id]))
            ->assertNotFound();
    }
}
