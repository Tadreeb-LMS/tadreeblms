<?php
namespace App\Exports;

use App\Models\Stripe\SubscribeCourse;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class InternalAttendanceReportExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function query()
    {
        $params = $this->params;
        $user_id = $params['user_id'] ?? null;
        $assign_from_date = $params['from'] ?? null;
        $assign_to_date = $params['to'] ?? null;
        $course_id = $params['course_id'] ?? null;
        //dd($params);

        return SubscribeCourse::with(['student', 'course', 'course.category'])
            ->whereHas('student', function ($query) use ($user_id) {
                $query->when($user_id, function ($q) use ($user_id) {
                    $q->where('id', $user_id);
                });
            })
            ->when($assign_from_date, function ($q) use ($assign_from_date, $assign_to_date) {
                if ($assign_to_date) {
                    $q->whereBetween('assign_date', [$assign_from_date, $assign_to_date]);
                } else {
                    $q->whereDate('assign_date', '>=', $assign_from_date);
                }
            })
            ->when($assign_from_date, function ($q) use ($assign_from_date, $assign_to_date) {
                if ($assign_to_date) {
                    $q->whereBetween('assign_date', [$assign_from_date, $assign_to_date]);
                } else {
                    $q->whereDate('assign_date', '>=', $assign_from_date);
                }
            })
            ->when($course_id, function ($q) use ($course_id) {
                $q->where('course_id', $course_id);
            })
            ->whereHas('course')
            ->orderByDesc('id');
    }

    public function headings(): array
    {
        return [
            'EID',
            'User Status',
            'UserName', 
            'Department', 
            'Position', 
            'Enrollment Type', 
            'Course Category', 
            'Course Code', 
            'Course Name', 
            'User Progress %', 
            'Progress Status', 
            'Assessment Score', 
            'Assessment Status', 
            'Trainer Name', 
            'Assignment Date', 
            'Due Date'
        ];
    }

    public function map($data): array
    {


        $progress_status = 'Not started';
        if ($data->assignment_progress >= 70) {
            $progress_status = 'Completed';
        } elseif ($data->assignment_progress > 0) {
            $progress_status = 'In progress';
        }

        $assesment_status = '';
        $assignment_score = '';
        if($data->has_assesment == 0) {
            $assesment_status = 'Not Applied';
            $assignment_score = '';
        } 
        else if($data->has_assesment == 1 && $data->assesment_taken == 0) {
            $assesment_status = 'Not Started';
            $assignment_score = '0%';
        } 
        else {
            $assesment_status = $data->assignment_status;
            $assignment_score = (string) $data->assignment_score;
        }

        return [
            optional($data->student)->emp_id,
            optional($data->student)->active == 0 ? "InActive" : "Active",
            optional($data->student)->first_name . ' ' . optional($data->student)->last_name,
            optional(optional($data->employeeProfile)->department_details)->title,
            optional($data->employeeProfile)->position,
            'Assigned',
            optional(optional($data->course)->category)->name,
            $data->course->course_code ?? '',
            $data->course->title ?? '',
            $data->assignment_progress ? $data->assignment_progress . '%' : '0%',
            $progress_status,
            $assignment_score ?? '',
            $assesment_status ?? '',
            $data->course_trainer_name ?? '',
            $this->formatDate($data->assign_date),
            $this->formatDate($data->due_date),
        ];
    }

    private function formatDate($date)
    {
        return ($date && $date !== '0000-00-00') ? Carbon::parse($date)->format('d-F-Y') : '';
    }

    public function chunkSize(): int
    {
        return 100; // Reduce this if still hitting memory issues
    }

    public function count()
    {
        return $this->query()->count();
    }
}
