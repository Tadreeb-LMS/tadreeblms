<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class EmployeeSampleExport implements FromArray
{
    public function array(): array
    {
        return [
            [
                'employee_id',
                'first_name',
                'last_name',
                'email',
                'department',
                'position',
                'status',
            ],
            [
                'EMP001',
                'Amit',
                'Sharma',
                'amit@test.com',
                'IT',
                'Developer',
                'active',
            ],
        ];
    }
}
