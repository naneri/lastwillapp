<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_with_heavy_markdown_but_short_visible_text_is_accepted(): void
    {
        $owner = User::factory()->create();

        $heading = '# '.str_repeat('A', 19990); // 2-char marker, 19990 visible chars — under 20000

        $response = $this->actingAs($owner)->patch('/record', ['body' => $heading]);

        $response->assertRedirect(route('record.edit'));
        $response->assertSessionDoesntHaveErrors();
        $this->assertSame($heading, $owner->record->body);
    }

    public function test_record_with_too_much_visible_text_is_rejected(): void
    {
        $owner = User::factory()->create();

        $body = str_repeat('A', 20001);

        $response = $this->actingAs($owner)->patch('/record', ['body' => $body]);

        $response->assertSessionHasErrors('body');
        $this->assertNull($owner->fresh()->record);
    }
}
