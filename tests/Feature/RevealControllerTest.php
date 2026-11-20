<?php

namespace Tests\Feature;

use App\Domain\Enums\Plan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevealControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function makeReveal(string $recordBody): \App\Domain\Models\Reveal
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);
        $owner->record()->create(['body' => $recordBody]);
        $willReceiver = $owner->willReceivers()->create(['rank' => 1, 'email' => 'first@example.com']);

        return $owner->reveals()->create([
            'will_receiver_id' => $willReceiver->id,
            'token' => 'test-reveal-token',
            'sent_at' => CarbonImmutable::now(),
        ]);
    }

    public function test_the_record_is_rendered_as_formatted_html(): void
    {
        $reveal = $this->makeReveal('**The safe combination** is with my sister.');

        $response = $this->get('/reveal/'.$reveal->token);

        $response->assertOk();
        $response->assertSee('<strong>The safe combination</strong>', false);
    }

    public function test_raw_html_in_the_record_is_escaped_not_executed(): void
    {
        $reveal = $this->makeReveal('<script>alert(1)</script>');

        $response = $this->get('/reveal/'.$reveal->token);

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
    }
}
