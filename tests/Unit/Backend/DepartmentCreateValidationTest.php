<?php

namespace Tests\Unit\Backend;

use PHPUnit\Framework\TestCase;

class DepartmentCreateValidationTest extends TestCase
{
    public function test_create_user_group_form_exposes_required_title_validation(): void
    {
        $view = file_get_contents(
            __DIR__ . '/../../../resources/views/backend/department/create.blade.php'
        );

        $this->assertStringContainsString('action="{{ route(\'admin.department.store\') }}"', $view);
        $this->assertStringContainsString('Title <span class="text-danger">*</span>', $view);
        $this->assertStringContainsString('class="form-control @error(\'title\') is-invalid @enderror"', $view);
        $this->assertStringContainsString('required', $view);
        $this->assertStringContainsString('aria-required="true"', $view);
        $this->assertStringContainsString('$errors->first(\'title\'', $view);
        $this->assertStringContainsString('if (!form.checkValidity())', $view);
        $this->assertStringContainsString('xhr.responseJSON.errors', $view);
    }
}
