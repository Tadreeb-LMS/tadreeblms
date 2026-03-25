<?php

namespace Tests\Feature;

use App\Models\Locale;
use Tests\TestCase;

class SwitchLanguageTest extends TestCase
{
    /** @test */
    public function the_language_can_be_switched()
    {
        Locale::create([
            'name' => 'German',
            'short_name' => 'de',
            'display_type' => 'ltr',
        ]);

        $response = $this->get('/lang/de');

        $response->assertSessionHas('locale', 'de');
    }
}
