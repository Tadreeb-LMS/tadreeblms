<?php

namespace App\Imports;

use App\Models\Auth\User;
use App\Models\EmployeeProfile;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeeImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Create user
        $user = User::create([
            'first_name' => $row['first_name'],
            'last_name'  => $row['last_name'],
            'email'      => $row['email'],
            'password'   => Hash::make('123456'),
            'active'     => $row['status'] ?? 1,
            'employee_type' => 'internal'
        ]);

        // Find department & position
        $department = Department::where('name', $row['department'])->first();
        $position   = Position::where('name', $row['position'])->first();

        // Create employee profile
        EmployeeProfile::create([
            'user_id'      => $user->id,
            'employee_id' => $row['employee_id'],
            'department_id' => optional($department)->id,
            'position_id'   => optional($position)->id,
        ]);

        return $user;
    }

    public function rules(): array
    {
        return [
            '*.employee_id' => 'required',
            '*.first_name'  => 'required',
            '*.last_name'   => 'required',
            '*.email'       => 'required|email|unique:users,email',
            '*.department' => 'required',
            '*.position'   => 'required',
        ];
    }
}
