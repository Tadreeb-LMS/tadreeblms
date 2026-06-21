<?php

namespace Tests\Unit\Backend;

use PHPUnit\Framework\TestCase;

class TrainerEmployeeLayoutParityTest extends TestCase
{
    public function test_trainer_index_uses_employee_index_layout_contract(): void
    {
        $view = file_get_contents(
            __DIR__ . '/../../../resources/views/backend/teachers/index.blade.php'
        );
        $controller = file_get_contents(
            __DIR__ . '/../../../app/Http/Controllers/Backend/Admin/TeachersController.php'
        );

        $this->assertStringContainsString('class="card" style="border: none;"', $view);
        $this->assertStringContainsString('class="custom-teacher-table table-striped"', $view);
        $this->assertStringContainsString("dom: \"<'table-controls'lfB>\"", $view);
        $this->assertStringContainsString("data: 'emp_id'", $view);
        $this->assertStringContainsString("data: 'position'", $view);
        $this->assertStringContainsString("$(document).on('change', '.switch-input'", $view);
        $this->assertStringContainsString("return '<div class=\"actions-cell\">'", $controller);
        $this->assertStringContainsString("return \$q->getDepartment() ?: '-';", $controller);
        $this->assertStringContainsString("->addColumn('position'", $controller);
    }
}
