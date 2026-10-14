<?php

namespace App\Services\Courses;

use App\Mail\CourseMeetingInvite;
use App\Models\Auth\User;
use App\Models\Course;
use App\Models\LiveSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CourseScheduleService
{
    public function validateScheduleRequest(Request $request): void
    {
        if ($request->schedule_type === 'daily') {
            $request->validate([
                'daily_time' => 'required',
                'daily_duration' => 'required|integer|min:1',
                'daily_repeat' => 'required|in:every_day,weekdays',
            ]);

            return;
        }

        if ($request->schedule_type === 'weekly') {
            $request->validate([
                'weekly_days' => 'required|array|min:1',
                'weekly_days.*' => 'integer|between:0,6',
                'weekly_time' => 'required',
                'weekly_duration' => 'required|integer|min:1',
            ], [
                'weekly_days.required' => 'Please select at least one day for weekly sessions.',
            ]);

            return;
        }

        if ($request->schedule_type === 'custom') {
            $request->validate([
                'custom_dates' => 'required|array|min:1',
                'custom_dates.*' => 'required|date',
                'custom_times' => 'required|array|min:1',
                'custom_times.*' => 'required',
                'custom_durations' => 'required|array|min:1',
                'custom_durations.*' => 'required|integer|min:1',
            ], [
                'custom_dates.required' => 'Please add at least one session.',
            ]);
        }
    }

    public function validateTrainerScheduleAvailability(
        Request $request,
        array $teacherIds,
        ?int $ignoreCourseId = null
    ): void {
        $course = new Course();
        $course->start_date = $request->start_date;
        $course->expire_at = $request->expire_at;

        foreach ($this->buildRequestedLiveSessions($course, $request) as $session) {
            $sessionStart = Carbon::parse($session['date'] . ' ' . $session['time']);
            $sessionEnd = $sessionStart->copy()->addMinutes((int) $session['duration']);

            $overlap = $this->findTrainerOverlappingSession(
                $teacherIds,
                $sessionStart,
                $sessionEnd,
                $ignoreCourseId
            );

            if ($overlap) {
                throw ValidationException::withMessages([
                    'schedule_type' => [
                        $this->formatTrainerOverlapMessage($overlap, $sessionStart, $sessionEnd),
                    ],
                ]);
            }
        }
    }

    public function trainerHasOverlappingSession(
        array $teacherIds,
        Carbon $start,
        Carbon $end,
        ?int $ignoreCourseId = null
    ): bool {
        return $this->findTrainerOverlappingSession($teacherIds, $start, $end, $ignoreCourseId) !== null;
    }

    public function findTrainerOverlappingSession(
        array $teacherIds,
        Carbon $start,
        Carbon $end,
        ?int $ignoreCourseId = null
    ): ?array {
        $teacherIds = array_filter($teacherIds);

        if (empty($teacherIds)) {
            return null;
        }

        $singleMeetings = DB::table('courses')
            ->join('course_user', 'courses.id', '=', 'course_user.course_id')
            ->whereIn('course_user.user_id', $teacherIds)
            ->whereNotNull('courses.meeting_start_at')
            ->whereNotNull('courses.meeting_duration');

        if ($ignoreCourseId) {
            $singleMeetings->where('courses.id', '!=', $ignoreCourseId);
        }

        foreach (
            $singleMeetings
                ->select(
                    'courses.id as course_id',
                    'courses.title as course_title',
                    'courses.meeting_start_at',
                    'courses.meeting_duration'
                )
                ->get() as $meeting
        ) {
            $meetingStart = Carbon::parse($meeting->meeting_start_at);
            $meetingEnd = $meetingStart->copy()->addMinutes((int) $meeting->meeting_duration);

            if ($this->timeRangesOverlap($start, $end, $meetingStart, $meetingEnd)) {
                return [
                    'course_id' => $meeting->course_id,
                    'course_title' => $meeting->course_title,
                    'session_start' => $meetingStart,
                    'session_end' => $meetingEnd,
                    'duration' => (int) $meeting->meeting_duration,
                    'source' => 'course_meeting',
                ];
            }
        }

        $liveSessions = DB::table('live_sessions')
            ->join('course_user', 'live_sessions.course_id', '=', 'course_user.course_id')
            ->join('courses', 'courses.id', '=', 'live_sessions.course_id')
            ->whereIn('course_user.user_id', $teacherIds);

        if ($ignoreCourseId) {
            $liveSessions->where('live_sessions.course_id', '!=', $ignoreCourseId);
        }

        foreach (
            $liveSessions
                ->select(
                    'live_sessions.course_id',
                    'courses.title as course_title',
                    'live_sessions.id as live_session_id',
                    'live_sessions.session_date',
                    'live_sessions.session_time',
                    'live_sessions.duration'
                )
                ->get() as $session
        ) {
            $sessionDate = Carbon::parse($session->session_date)->format('Y-m-d');
            $sessionStart = Carbon::parse($sessionDate . ' ' . $session->session_time);
            $sessionEnd = $sessionStart->copy()->addMinutes((int) $session->duration);

            if ($this->timeRangesOverlap($start, $end, $sessionStart, $sessionEnd)) {
                return [
                    'course_id' => $session->course_id,
                    'course_title' => $session->course_title,
                    'live_session_id' => $session->live_session_id,
                    'session_start' => $sessionStart,
                    'session_end' => $sessionEnd,
                    'duration' => (int) $session->duration,
                    'source' => 'live_session',
                ];
            }
        }

        return null;
    }

    public function formatTrainerOverlapMessage(
        array $overlap,
        Carbon $requestedStart,
        Carbon $requestedEnd
    ): string {
        $courseTitle = $overlap['course_title'] ?? 'another course';
        $existingStart = $overlap['session_start'];
        $existingEnd = $overlap['session_end'];

        return sprintf(
            'Trainer is already assigned for another course at this time. Conflict with "%s" on %s from %s to %s. Requested session: %s to %s.',
            $courseTitle,
            $existingStart->format('Y-m-d'),
            $existingStart->format('H:i'),
            $existingEnd->format('H:i'),
            $requestedStart->format('Y-m-d H:i'),
            $requestedEnd->format('H:i')
        );
    }

    public function buildRequestedLiveSessions(Course $course, Request $request): array
    {
        $scheduleType = $request->schedule_type;

        $startDateValue = $course->getRawOriginal('start_date')
            ?: ($course->getAttributes()['start_date'] ?? $request->start_date);

        $endDateValue = $course->getRawOriginal('expire_at')
            ?: ($course->getAttributes()['expire_at'] ?? $request->expire_at);

        $startDate = Carbon::parse($startDateValue);
        $endDate = Carbon::parse($endDateValue);

        $sessions = [];

        if ($scheduleType === 'daily') {
            $time = $request->daily_time;
            $duration = (int) ($request->daily_duration ?? 60);
            $repeat = $request->daily_repeat ?? 'every_day';

            $current = $startDate->copy();

            while ($current->lte($endDate)) {
                $isWeekday = $current->isWeekday();

                if ($repeat === 'every_day' || ($repeat === 'weekdays' && $isWeekday)) {
                    $sessions[] = [
                        'date' => $current->format('Y-m-d'),
                        'time' => $time,
                        'duration' => $duration,
                    ];
                }

                $current->addDay();
            }

            return $sessions;
        }

        if ($scheduleType === 'weekly') {
            $time = $request->weekly_time;
            $duration = (int) ($request->weekly_duration ?? 60);
            $selectedDays = array_map('intval', $request->weekly_days ?? []);

            $current = $startDate->copy();

            while ($current->lte($endDate)) {
                if (in_array($current->dayOfWeek, $selectedDays, true)) {
                    $sessions[] = [
                        'date' => $current->format('Y-m-d'),
                        'time' => $time,
                        'duration' => $duration,
                    ];
                }

                $current->addDay();
            }

            return $sessions;
        }

        if ($scheduleType === 'custom') {
            $dates = $request->custom_dates ?? [];
            $times = $request->custom_times ?? [];
            $durations = $request->custom_durations ?? [];

            foreach ($dates as $i => $date) {
                if (!$date || !isset($times[$i])) {
                    continue;
                }

                $sessionDate = Carbon::parse($date);

                if ($sessionDate->lt($startDate) || $sessionDate->gt($endDate)) {
                    continue;
                }

                $sessions[] = [
                    'date' => $date,
                    'time' => $times[$i],
                    'duration' => (int) ($durations[$i] ?? 60),
                ];
            }
        }

        return $sessions;
    }

    private function timeRangesOverlap(
        Carbon $startA,
        Carbon $endA,
        Carbon $startB,
        Carbon $endB
    ): bool {
        return $startA->lt($endB) && $endA->gt($startB);
    }

    public function generateLiveSessions(Course $course, Request $request): int
{
    $course->liveSessions()->delete();

    $provider = $request->meeting_provider;
    $timezone = $request->meeting_timezone ?? 'Asia/Riyadh';
    $scheduleType = $request->schedule_type;

    $sessions = $this->buildRequestedLiveSessions($course, $request);

    $lastSessionDate = null;
    $failedCount = 0;

    foreach ($sessions as $session) {
        $sessionDateTime = $session['date'] . ' ' . $session['time'] . ':00';

        $meetingLink = null;
        $meetingId = null;
        $hostUrl = null;

        $meetingRequest = new Request();
        $meetingRequest->merge([
            'meeting_start_at' => $sessionDateTime,
            'meeting_duration' => $session['duration'],
            'meeting_timezone' => $timezone,
        ]);

        try {
            $meetingData = $this->createMeetingViaModule($provider, $meetingRequest, $course);

            if ($meetingData) {
                $meetingLink = $meetingData['meeting_join_url'] ?? null;
                $meetingId = $meetingData['meeting_id'] ?? null;
                $hostUrl = $meetingData['meeting_host_url'] ?? null;
            }

            if (!$meetingLink) {
                $failedCount++;
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to create meeting for session {$session['date']}: " . $e->getMessage());
            $failedCount++;
        }

        LiveSession::create([
            'course_id' => $course->id,
            'provider' => $provider,
            'session_date' => $session['date'],
            'session_time' => $session['time'],
            'meeting_link' => $meetingLink,
            'meeting_id' => $meetingId,
            'host_url' => $hostUrl,
            'duration' => $session['duration'],
            'recurrence_type' => $scheduleType,
            'created_by' => Auth::id(),
        ]);

        $lastSessionDate = $session['date'];
    }

    if ($lastSessionDate) {
        $course->last_session_date = $lastSessionDate;
        $course->save();
    }

    return $failedCount;
}

public function regenerateMissingMeetingLinks(Course $course): array
{
    $sessions = $course->liveSessions()->whereNull('meeting_link')->get();

    if ($sessions->isEmpty()) {
        return [
            'success_count' => 0,
            'failed_count' => 0,
            'has_missing_sessions' => false,
            'provider' => $course->meeting_provider,
        ];
    }

    $provider = $course->meeting_provider;
    $timezone = $course->meeting_timezone ?? 'Asia/Riyadh';
    $successCount = 0;
    $failedCount = 0;

    foreach ($sessions as $session) {
        $timeFormatted = Carbon::parse($session->session_time)->format('H:i:s');
        $sessionDateTime = $session->session_date->format('Y-m-d') . ' ' . $timeFormatted;

        $meetingRequest = new Request();
        $meetingRequest->merge([
            'meeting_start_at' => $sessionDateTime,
            'meeting_duration' => $session->duration,
            'meeting_timezone' => $timezone,
        ]);

        try {
            $meetingData = $this->createMeetingViaModule($provider, $meetingRequest, $course);

            if ($meetingData) {
                $session->update([
                    'meeting_link' => $meetingData['meeting_join_url'] ?? null,
                    'meeting_id' => $meetingData['meeting_id'] ?? null,
                    'host_url' => $meetingData['meeting_host_url'] ?? null,
                ]);

                $successCount++;
            } else {
                $failedCount++;
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to regenerate meeting for session {$session->id}: " . $e->getMessage());
            $failedCount++;
        }
    }

    return [
        'success_count' => $successCount,
        'failed_count' => $failedCount,
        'has_missing_sessions' => true,
        'provider' => $provider,
    ];
}

public function createMeetingViaModule(string $provider, Request $request, Course $course): ?array
{
    if ($provider === 'zoom') {
        $service = new \Modules\Zoom\Services\ZoomMeetingService();

        $meeting = $service->createMeeting(
            $course->title,
            $request->meeting_start_at,
            $request->meeting_duration,
            $request->meeting_timezone
        );

        if ($meeting) {
            return [
                'meeting_id' => $meeting['id'],
                'meeting_join_url' => $meeting['join_url'],
                'meeting_host_url' => $meeting['host_url'] ?? null,
            ];
        }
    } elseif ($provider === 'teams') {
        $service = new \Modules\Teams\Services\TeamsMeetingService();

        $meeting = $service->createMeeting(
            $course->title,
            $request->meeting_start_at,
            $request->meeting_duration,
            $request->meeting_timezone
        );

        if ($meeting) {
            return [
                'meeting_id' => $meeting['id'],
                'meeting_join_url' => $meeting['join_url'],
                'meeting_host_url' => $meeting['host_url'] ?? null,
            ];
        }
    } elseif (in_array($provider, ['google-meet-integration', 'google_meet'], true)) {
        $service = new \Modules\GoogleMeetIntegration\Services\GoogleMeetService();

        $course->load(['teachers', 'students']);

        $teacherEmails = $course->teachers->pluck('email')->filter()->values()->toArray();
        $studentEmails = $course->students->pluck('email')->filter()->values()->toArray();

        $hostEmail = $teacherEmails[0] ?? null;
        $attendees = array_values(array_unique(array_merge($teacherEmails, $studentEmails)));

        $meeting = $service->createMeeting(
            $course->title,
            $request->meeting_start_at,
            $request->meeting_duration,
            $request->meeting_timezone,
            $hostEmail,
            $attendees
        );

        if ($meeting) {
            return [
                'meeting_id' => $meeting['id'],
                'meeting_join_url' => $meeting['join_url'],
                'meeting_host_url' => $meeting['host_url'] ?? null,
            ];
        }
    }

    return null;
}

public function sendMeetingInviteToStudents(Course $course, array $studentIds): void
{
    $students = User::whereIn('id', $studentIds)->get();

    if ($course->meeting_provider === 'google-meet-integration' && $course->meeting_id) {
        $service = new \Modules\GoogleMeetIntegration\Services\GoogleMeetService();

        $course->loadMissing('teachers');

        $hostEmail = $course->teachers->first()->email ?? null;

        foreach ($students as $student) {
            $service->addAttendeeToMeeting($course->meeting_id, $student->email, $hostEmail);
        }
    }

    foreach ($students as $student) {
        Mail::to($student->email)->send(new CourseMeetingInvite($course));
    }
}

}