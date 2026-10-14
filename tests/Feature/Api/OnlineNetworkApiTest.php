<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * API v1 online networks - reference data for online_network_id on PIREPs.
 */
class OnlineNetworkApiTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_listing_requires_authentication(): void
    {
        $this->getJson(route('api.v1.online-networks.index'))
            ->assertUnauthorized();
    }

    public function test_any_user_can_list_online_networks_unpaginated(): void
    {
        DB::table('online_networks')->insert(['id' => 2, 'networkname' => 'IVAO']);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('api.v1.online-networks.index'))
            ->assertOk()
            ->assertExactJson(['data' => [
                ['id' => 1, 'name' => 'VATSIM'],
                ['id' => 2, 'name' => 'IVAO'],
            ]]);
    }
}
