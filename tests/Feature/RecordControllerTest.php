<?php

namespace Tests\Feature;

use App\Domain\Enums\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_with_heavy_markdown_but_short_visible_text_is_accepted(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);

        $heading = '# '.str_repeat('A', 29990); // 2-char marker, 29990 visible chars — under Basic's 30,000

        $response = $this->actingAs($owner)->patch('/record', ['body' => $heading]);

        $response->assertRedirect(route('record.edit'));
        $response->assertSessionDoesntHaveErrors();
        $this->assertSame($heading, $owner->record->body);
    }

    public function test_record_with_too_much_visible_text_is_rejected(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Basic]);

        $body = str_repeat('A', 30001);

        $response = $this->actingAs($owner)->patch('/record', ['body' => $body]);

        $response->assertSessionHasErrors('body');
        $this->assertNull($owner->fresh()->record);
    }

    public function test_free_plan_is_capped_at_1000_visible_characters(): void
    {
        $owner = User::factory()->create(['plan' => Plan::Free]);

        $this->actingAs($owner)->patch('/record', ['body' => str_repeat('A', 1000)])
            ->assertSessionDoesntHaveErrors();

        $this->actingAs($owner)->patch('/record', ['body' => str_repeat('A', 1001)])
            ->assertSessionHasErrors('body');
    }
}
