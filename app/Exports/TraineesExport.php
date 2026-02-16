<?php

namespace App\Exports;

use App\Models\Auth\User;
use App\Models\Stripe\SubscribeCourse;
use CustomHelper;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class TraineesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $records = User::query()->role('student')->groupBy('email')->orderBy('created_at', 'desc')->get();
        return $records;  // Modify this to filter data if needed
    }

    public function headings(): array
    {
        return ['ID', 'Employee Id', 'First Name', 'Last Name', 'Email Address', 'Department', 'Position', 'Status'];
    }

    public function map($data): array
    {
        $status = $data->active ? 'Active' : "In-active";

        return [
            @$data->id,
            @$data->emp_id,
            @$data->first_name,
            @$$data->last_name,
            @$data->email,
            @$data->getDepartment(),
            @$data->getPosition(),
            @$status,
        ];
    }
}
