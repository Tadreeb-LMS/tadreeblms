<?php

namespace Tests\Unit\Backend;

use PHPUnit\Framework\TestCase;

class InternalAttendanceReportBladeTest extends TestCase
{
    /** @test */
    public function the_internal_attendance_report_has_balanced_blade_stacks()
    {
        $view = file_get_contents(__DIR__.'/../../../resources/views/backend/employee/internal_attendace_report.blade.php');

        $this->assertSame(substr_count($view, '@push('), substr_count($view, '@endpush'));
        $this->assertSame(1, substr_count($view, "@push('after-scripts')"));
        $this->assertSame(1, substr_count($view, "@push('after-styles')"));
    }
}
