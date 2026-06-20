<?php

namespace Tests\Unit;

use App\Models\LiveSession;
use PHPUnit\Framework\TestCase;

class LiveSessionMeetingProviderTest extends TestCase
{
    /** @test */
    public function it_normalizes_supported_meeting_providers(): void
    {
        $this->assertSame('zoom', LiveSession::normalizeMeetingProvider('zoom'));
        $this->assertSame('teams', LiveSession::normalizeMeetingProvider('teams'));
        $this->assertSame('google_meet', LiveSession::normalizeMeetingProvider('google_meet'));
        $this->assertSame('google_meet', LiveSession::normalizeMeetingProvider('google-meet-integration'));
    }

    /** @test */
    public function it_rejects_invalid_meeting_providers(): void
    {
        $this->assertNull(LiveSession::normalizeMeetingProvider(null));
        $this->assertNull(LiveSession::normalizeMeetingProvider('webex'));
    }

    /** @test */
    public function it_falls_back_to_the_legacy_provider_attribute(): void
    {
        $session = new LiveSession(['provider' => 'google-meet-integration']);

        $this->assertSame('google_meet', $session->meeting_provider);
    }
}
