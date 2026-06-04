<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Services\LocaleService;
use Spatie\Permission\Models\Permission;
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
            'is_enabled' => 1,
        ]);

        $response = $this->get('/lang/de');

        $response->assertSessionHas('locale', 'de');
    }

    /** @test */
    public function disabled_languages_are_not_supported()
    {
        Locale::create([
            'name' => 'English',
            'short_name' => 'en',
            'display_type' => 'ltr',
            'is_default' => 1,
            'is_enabled' => 1,
        ]);

        Locale::create([
            'name' => 'German',
            'short_name' => 'de',
            'display_type' => 'ltr',
            'is_enabled' => 0,
        ]);

        $this->assertSame(['en'], app(LocaleService::class)->supportedLocales());
    }

    /** @test */
    public function disabled_languages_cannot_be_selected_from_the_swap_route()
    {
        Locale::create([
            'name' => 'English',
            'short_name' => 'en',
            'display_type' => 'ltr',
            'is_default' => 1,
            'is_enabled' => 1,
        ]);

        Locale::create([
            'name' => 'German',
            'short_name' => 'de',
            'display_type' => 'ltr',
            'is_enabled' => 0,
        ]);

        $response = $this->get('/lang/de');

        $response->assertSessionHas('locale', 'en');
    }

    /** @test */
    public function language_settings_toggle_persists_disabled_status()
    {
        $admin = $this->loginAsAdmin();
        $admin->givePermissionTo(factory(Permission::class)->create(['name' => 'trainer_access']));

        Locale::create([
            'name' => 'English',
            'short_name' => 'en',
            'display_type' => 'ltr',
            'is_default' => 1,
            'is_enabled' => 1,
        ]);

        Locale::create([
            'name' => 'German',
            'short_name' => 'de',
            'display_type' => 'ltr',
            'is_enabled' => 1,
        ]);

        $this->post(route('admin.general-settings'), [
            'language_action' => 'toggle:de:0',
        ]);

        $this->assertDatabaseHas('locales', [
            'short_name' => 'de',
            'is_enabled' => 0,
        ]);
    }
}
