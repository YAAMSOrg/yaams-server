<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\PirepAccepted;
use App\Notifications\PirepFiled;
use App\Notifications\TwoFactorReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * API v1 notifications - list, unread count and acknowledging, always scoped
 * to the token owner.
 */
class NotificationApiTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    private function notify(User $user, string $type = PirepFiled::class, array $data = [], ?string $readAt = null, string $createdAt = '2026-01-01 12:00:00'): DatabaseNotification
    {
        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'data' => array_merge(['title' => 'Title', 'message' => 'Message', 'url' => 'https://example.com/flights/1'], $data),
            'read_at' => $readAt,
        ]);
        $notification->forceFill(['created_at' => $createdAt])->save();

        return $notification;
    }

    public function test_endpoints_require_authentication(): void
    {
        $id = (string) Str::uuid();

        $this->getJson(route('api.v1.notifications.index'))->assertUnauthorized();
        $this->getJson(route('api.v1.notifications.unread-count'))->assertUnauthorized();
        $this->postJson(route('api.v1.notifications.read', $id))->assertUnauthorized();
        $this->postJson(route('api.v1.notifications.read-all'))->assertUnauthorized();
    }

    public function test_list_shows_only_own_notifications_newest_first(): void
    {
        $user = User::factory()->create();
        $older = $this->notify($user, PirepAccepted::class, ['title' => 'PIREP accepted'], '2026-01-02 08:00:00', '2026-01-01 10:00:00');
        $newer = $this->notify($user, TwoFactorReset::class, ['url' => null], null, '2026-01-01 11:00:00');
        $this->notify(User::factory()->create());
        Sanctum::actingAs($user);

        $this->getJson(route('api.v1.notifications.index'))
            ->assertOk()
            ->assertJsonPath('data.*.id', [$newer->id, $older->id])
            ->assertJsonPath('data.0.type', 'twoFactorReset')
            ->assertJsonPath('data.0.url', null)
            ->assertJsonPath('data.0.readAt', null)
            ->assertJsonPath('data.1.type', 'pirepAccepted')
            ->assertJsonPath('data.1.title', 'PIREP accepted')
            ->assertJsonPath('data.1.message', 'Message')
            ->assertJsonPath('data.1.url', 'https://example.com/flights/1')
            ->assertJsonPath('data.1.readAt', '2026-01-02T08:00:00.000000Z')
            ->assertJsonPath('data.1.createdAt', '2026-01-01T10:00:00.000000Z')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonMissingPath('meta.links')
            ->assertJsonStructure(['links' => ['first', 'last', 'prev', 'next']]);
    }

    public function test_list_can_be_filtered_and_sorted(): void
    {
        $user = User::factory()->create();
        $read = $this->notify($user, readAt: '2026-01-03 00:00:00', createdAt: '2026-01-01 10:00:00');
        $unread = $this->notify($user, createdAt: '2026-01-01 11:00:00');
        Sanctum::actingAs($user);

        $this->getJson(route('api.v1.notifications.index', ['unread' => 'true']))
            ->assertJsonPath('data.*.id', [$unread->id]);
        $this->getJson(route('api.v1.notifications.index', ['unread' => '0']))
            ->assertJsonPath('data.*.id', [$read->id]);
        $this->getJson(route('api.v1.notifications.index', ['sort' => 'created_at']))
            ->assertJsonPath('data.*.id', [$read->id, $unread->id]);

        $this->getJson(route('api.v1.notifications.index', ['unread' => 'maybe']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('unread');
        $this->getJson(route('api.v1.notifications.index', ['sort' => 'type']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sort');
    }

    public function test_unread_count_counts_only_own_unread(): void
    {
        $user = User::factory()->create();
        $this->notify($user);
        $this->notify($user);
        $this->notify($user, readAt: '2026-01-02 00:00:00');
        $this->notify(User::factory()->create());
        Sanctum::actingAs($user);

        $this->getJson(route('api.v1.notifications.unread-count'))
            ->assertOk()
            ->assertExactJson(['count' => 2]);
    }

    public function test_marking_one_as_read_is_idempotent(): void
    {
        $user = User::factory()->create();
        $notification = $this->notify($user);
        Sanctum::actingAs($user);

        $this->travelTo('2026-02-01 09:00:00');
        $this->postJson(route('api.v1.notifications.read', $notification->id))
            ->assertOk()
            ->assertJsonPath('data.id', $notification->id)
            ->assertJsonPath('data.readAt', '2026-02-01T09:00:00.000000Z');

        $this->travelTo('2026-02-02 09:00:00');
        $this->postJson(route('api.v1.notifications.read', $notification->id))
            ->assertOk()
            ->assertJsonPath('data.readAt', '2026-02-01T09:00:00.000000Z');
    }

    public function test_cannot_read_another_users_or_unknown_notification(): void
    {
        $theirs = $this->notify(User::factory()->create());
        Sanctum::actingAs(User::factory()->create());

        $this->postJson(route('api.v1.notifications.read', $theirs->id))->assertNotFound();
        $this->postJson(route('api.v1.notifications.read', (string) Str::uuid()))->assertNotFound();
        $this->postJson('/api/v1/notifications/not-a-uuid/read')->assertNotFound();

        $this->assertNull($theirs->fresh()->read_at);
    }

    public function test_read_all_marks_only_own_notifications(): void
    {
        $user = User::factory()->create();
        $this->notify($user);
        $this->notify($user);
        $theirs = $this->notify(User::factory()->create());
        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.notifications.read-all'))->assertNoContent();

        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->assertNull($theirs->fresh()->read_at);
    }
}
