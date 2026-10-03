<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * resources/views/includes/partials/messages.blade.php renders the flash
 * alert banners shown at the top of every backend page.
 *
 * Laravel's RedirectResponse::__call() turns any with*() call into a session
 * key via Str::snake(): withFlashDanger() sets 'flash_danger', withFlashSuccess()
 * sets 'flash_success', withFlashWarning() sets 'flash_warning', withFlashInfo()
 * sets 'flash_info'. Controllers across the app also call ->with('success', ...)
 * directly. The partial only rendered 'flash_success', 'error', 'warning' and
 * 'info' - so withFlashDanger() (58 call sites), ->with('success', ...) (17),
 * withFlashWarning() (3) and withFlashInfo() (2) silently produced no banner
 * at all, even though the redirect and the session write both succeeded.
 *
 * Each assertion here uses a unique marker string so a passing test can only
 * mean the key that was set is the one that rendered - not a coincidental
 * match against unrelated page content.
 */
class FlashMessageSessionKeysTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('role_access', function () {
            return true;
        });

        View::share('locales', []);
    }

    public static function flashKeyProvider(): array
    {
        return [
            'flash_success (withFlashSuccess)' => ['flash_success', 'alert-success'],
            'success (with(\'success\', ...))' => ['success', 'alert-success'],
            'error (with(\'error\', ...))' => ['error', 'alert-danger'],
            'flash_danger (withFlashDanger)' => ['flash_danger', 'alert-danger'],
            'warning (with(\'warning\', ...))' => ['warning', 'alert-warning'],
            'flash_warning (withFlashWarning)' => ['flash_warning', 'alert-warning'],
            'info (with(\'info\', ...))' => ['info', 'alert-info'],
            'flash_info (withFlashInfo)' => ['flash_info', 'alert-info'],
        ];
    }

    #[Test]
    public function each_session_flash_key_renders_its_message_in_the_matching_alert_style()
    {
        $this->loginAsAdmin();

        $allKeys = array_column(self::flashKeyProvider(), 0);

        foreach (self::flashKeyProvider() as $description => [$sessionKey, $expectedAlertClass]) {
            $marker = 'FLASH-MARKER-' . strtoupper($sessionKey) . '-' . bin2hex(random_bytes(4));

            // withSession() merges into the persistent test session rather
            // than following Laravel's real "flash for one request" aging,
            // so a key left over from an earlier iteration would otherwise
            // leak into this one. Forget() removes the array key entirely -
            // Blade's session($key, $default) only falls back to $default
            // when the key is genuinely absent, not when it's present-but-null,
            // so setting leftover keys to null here (instead of forgetting
            // them) would silently break this test without the fix being wrong.
            session()->forget($allKeys);
            // forget() only mutates the current in-memory Store; the test's
            // 'array' session driver reloads from its own persisted storage
            // on the next request unless we explicitly save() first.
            session()->save();

            $html = $this->withSession([$sessionKey => $marker])
                ->get(route('admin.roles.index'))
                ->assertOk()
                ->getContent();

            $this->assertStringContainsString(
                $marker,
                $html,
                "Session key '{$sessionKey}' ({$description}) did not render in the page."
            );

            // Loosely confirm the marker landed inside the right alert style,
            // without depending on exact whitespace/attribute order.
            $snippetStart = max(0, strpos($html, $marker) - 400);
            $snippet = substr($html, $snippetStart, 800);
            $this->assertStringContainsString(
                $expectedAlertClass,
                $snippet,
                "Session key '{$sessionKey}' rendered, but not inside a '{$expectedAlertClass}' alert."
            );
        }
    }

    #[Test]
    public function a_real_controller_using_withflashdanger_renders_its_message()
    {
        // End-to-end: TestQuestionController::create() calls
        // ->withFlashDanger(...) when no course is selected. This exercises
        // Laravel's real __call() -> Str::snake() conversion (not a
        // hand-set session key) together with the partial fix.
        foreach (['question_access', 'question_create', 'test_access'] as $gate) {
            Gate::define($gate, function () {
                return true;
            });
        }

        $this->loginAsAdmin();

        $redirect = $this->get(route('admin.test_questions.create'));
        $redirect->assertRedirect(route('admin.test_questions.index'));

        $html = $this->get($redirect->headers->get('Location'))->assertOk()->getContent();

        // assertSee() alone is not enough here: the same literal string is
        // also hardcoded in this page's inline JS (a disabled-button alert()
        // in test_questions/index.blade.php), unconditionally, on every load.
        // A naive string-presence check would pass even without the fix.
        // Require it inside the rendered alert-danger banner specifically.
        $message = 'Please select a course before adding a new question.';
        $this->assertStringContainsString($message, $html);

        $position = strpos($html, $message);
        $snippet = substr($html, max(0, $position - 400), 800);
        $this->assertStringContainsString(
            'alert-danger',
            $snippet,
            'Message text is present on the page, but not inside the alert-danger banner '
                . '(likely only matching the unrelated inline JS alert() on this page).'
        );
    }
}
