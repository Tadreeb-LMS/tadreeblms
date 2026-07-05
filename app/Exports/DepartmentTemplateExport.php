<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class DepartmentTemplateExport implements FromArray
{
    public function array(): array
    {
        return [
            ['title'],
            ['Human Resources'],
            ['Information Technology'],
            ['Finance'],
        ];
    }
}