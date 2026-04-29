<?php

namespace App\Services\Kpi;

use App\Models\Auth\User;
use App\Models\Kpi;
use Carbon\Carbon;
use Generator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KpiExportDatasetService
{
    /**
     * @param array $filters
     * @return int
     */
    public function countUsers(array $filters): int
    {
        return $this->buildUsersQuery($filters)->count();
    }

    /**
     * @param array $filters
     * @return int
     */
    public function countKpis(array $filters): int
    {
        return $this->buildKpiQuery($filters)->count();
    }

    /**
     * @param array $filters
     * @param callable|null $progressCallback
     * @return Generator
     */
    public function generateRows(array $filters, callable $progressCallback = null): Generator
    {
        $dateFrom = !empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : null;
        $dateTo = !empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : null;

        $kpis = $this->buildKpiQuery($filters)->get();
        $totalActiveWeight = max(0.01, (float) $kpis->sum('weight'));
        $roleFilter = trim((string) ($filters['role'] ?? ''));
        $totalUsers = $this->countUsers($filters);
        $processedUsers = 0;

        $chunk = [];
        foreach ($this->buildUsersQuery($filters)->orderBy('users.id')->cursor() as $user) {
            $chunk[] = $user;

            if (count($chunk) >= 300) {
                yield from $this->buildRowsForUserChunk($chunk, $kpis, $dateFrom, $dateTo, $roleFilter, $totalActiveWeight);

                $processedUsers += count($chunk);
                if ($progressCallback) {
                    $progressCallback($processedUsers, max(1, $totalUsers));
                }
                $chunk = [];
            }
        }

        if (!empty($chunk)) {
            yield from $this->buildRowsForUserChunk($chunk, $kpis, $dateFrom, $dateTo, $roleFilter, $totalActiveWeight);
            $processedUsers += count($chunk);
            if ($progressCallback) {
                $progressCallback($processedUsers, max(1, $totalUsers));
            }
        }
    }

    /**
     * @param array $users
     * @param \Illuminate\Support\Collection $kpis
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @param string $roleFilter
     * @param float $totalActiveWeight
     * @return Generator
     */
    protected function buildRowsForUserChunk(array $users, $kpis, ?Carbon $dateFrom, ?Carbon $dateTo, string $roleFilter, float $totalActiveWeight): Generator
    {
        $userIds = collect($users)->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        $userNames = [];
        $userEmails = [];
        foreach ($users as $user) {
            $fullName = trim((string) $user->first_name . ' ' . (string) $user->last_name);
            $userNames[(int) $user->id] = $fullName !== '' ? $fullName : ((string) $user->email ?: ('User #' . $user->id));
            $userEmails[(int) $user->id] = (string) $user->email;
        }

        foreach ($kpis as $kpi) {
            $valuesByUser = $this->getValuesByUserForType(
                (string) $kpi->type,
                $userIds,
                method_exists($kpi, 'resolveScopedCourseIds') ? $kpi->resolveScopedCourseIds() : [],
                $dateFrom,
                $dateTo
            );

            foreach ($valuesByUser as $userId => $metricValue) {
                if ($metricValue === null) {
                    continue;
                }

                $weightedScore = round(((float) $metricValue * (float) $kpi->weight) / $totalActiveWeight, 2);

                yield [
                    $roleFilter !== '' ? $roleFilter : 'all',
                    (int) $userId,
                    $userNames[(int) $userId] ?? ('User #' . $userId),
                    $userEmails[(int) $userId] ?? '',
                    (int) $kpi->id,
                    (string) $kpi->code,
                    (string) $kpi->name,
                    (string) $kpi->type,
                    round((float) $metricValue, 2),
                    round((float) $kpi->weight, 2),
                    $weightedScore,
                    $dateFrom ? $dateFrom->toDateString() : '',
                    $dateTo ? $dateTo->toDateString() : '',
                ];
            }
        }
    }

    /**
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildUsersQuery(array $filters)
    {
        $role = trim((string) ($filters['role'] ?? ''));

        $query = User::query()
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email')
            ->whereNull('users.deleted_at');

        if ($role !== '') {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        return $query;
    }

    /**
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildKpiQuery(array $filters)
    {
        $kpiIds = collect((array) ($filters['kpi_ids'] ?? []))
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $query = Kpi::query()
            ->where('is_active', true)
            ->select('id', 'name', 'code', 'type', 'weight')
            ->with('categories:id,name', 'courses:id');

        if (!empty($kpiIds)) {
            $query->whereIn('id', $kpiIds);
        }

        return $query;
    }

    /**
     * @param string $type
     * @param array $userIds
     * @param array $courseIds
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @return array<int, float|null>
     */
    protected function getValuesByUserForType(string $type, array $userIds, array $courseIds, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        $result = [];
        foreach ($userIds as $userId) {
            $result[(int) $userId] = null;
        }

        if (empty($userIds)) {
            return $result;
        }

        $metricRows = [];
        switch ($type) {
            case 'completion':
                $metricRows = $this->completionByUser($userIds, $courseIds, $dateFrom, $dateTo);
                break;
            case 'score':
                $metricRows = $this->scoreByUser($userIds, $courseIds, $dateFrom, $dateTo);
                break;
            case 'activity':
                $metricRows = $this->activityByUser($userIds, $dateFrom, $dateTo);
                break;
            case 'time':
                $metricRows = $this->timeByUser($userIds, $dateFrom, $dateTo);
                break;
            default:
                return $result;
        }

        foreach ($metricRows as $userId => $value) {
            $id = (int) $userId;
            if (!array_key_exists($id, $result)) {
                continue;
            }
            $result[$id] = round((float) $value, 2);
        }

        return $result;
    }

    protected function completionByUser(array $userIds, array $courseIds, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        if (!Schema::hasTable('subscribe_courses') || !Schema::hasColumn('subscribe_courses', 'user_id')) {
            return [];
        }

        $query = DB::table('subscribe_courses')
            ->select('user_id', DB::raw('AVG(CASE WHEN is_completed = 1 THEN 100 ELSE 0 END) as metric'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id');

        if (Schema::hasColumn('subscribe_courses', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (!empty($courseIds) && Schema::hasColumn('subscribe_courses', 'course_id')) {
            $query->whereIn('course_id', $courseIds);
        }

        if (Schema::hasColumn('subscribe_courses', 'completed_at')) {
            $this->applyDateRange($query, 'completed_at', $dateFrom, $dateTo);
        } elseif (Schema::hasColumn('subscribe_courses', 'updated_at')) {
            $this->applyDateRange($query, 'updated_at', $dateFrom, $dateTo);
        } elseif (Schema::hasColumn('subscribe_courses', 'created_at')) {
            $this->applyDateRange($query, 'created_at', $dateFrom, $dateTo);
        }

        return $query->pluck('metric', 'user_id')->toArray();
    }

    protected function scoreByUser(array $userIds, array $courseIds, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        if (!Schema::hasTable('tests_results') || !Schema::hasColumn('tests_results', 'user_id')) {
            return [];
        }

        $query = DB::table('tests_results')
            ->select('tests_results.user_id', DB::raw('AVG(tests_results.test_result) as metric'))
            ->whereIn('tests_results.user_id', $userIds)
            ->groupBy('tests_results.user_id');

        if (Schema::hasTable('tests') && Schema::hasColumn('tests_results', 'test_id') && Schema::hasColumn('tests', 'id')) {
            $query->join('tests', 'tests.id', '=', 'tests_results.test_id');
            if (!empty($courseIds) && Schema::hasColumn('tests', 'course_id')) {
                $query->whereIn('tests.course_id', $courseIds);
            }
            if (Schema::hasColumn('tests', 'deleted_at')) {
                $query->whereNull('tests.deleted_at');
            }
        }

        if (Schema::hasColumn('tests_results', 'created_at')) {
            $this->applyDateRange($query, 'tests_results.created_at', $dateFrom, $dateTo);
        }

        return $query->pluck('metric', 'tests_results.user_id')->toArray();
    }

    protected function activityByUser(array $userIds, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        if (Schema::hasTable('lms_kpi_events') && Schema::hasColumn('lms_kpi_events', 'user_id')) {
            $query = DB::table('lms_kpi_events')
                ->select('user_id', DB::raw('LEAST(100, COUNT(*) * 10) as metric'))
                ->whereIn('user_id', $userIds)
                ->groupBy('user_id');

            if (Schema::hasColumn('lms_kpi_events', 'occurred_at')) {
                $this->applyDateRange($query, 'occurred_at', $dateFrom, $dateTo);
            }

            return $query->pluck('metric', 'user_id')->toArray();
        }

        return [];
    }

    protected function timeByUser(array $userIds, ?Carbon $dateFrom, ?Carbon $dateTo): array
    {
        if (!Schema::hasTable('video_progresses') || !Schema::hasColumn('video_progresses', 'user_id')) {
            return [];
        }

        $query = DB::table('video_progresses')
            ->select('user_id', DB::raw('LEAST(100, SUM(progress) / 60) as metric'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id');

        if (Schema::hasColumn('video_progresses', 'updated_at')) {
            $this->applyDateRange($query, 'updated_at', $dateFrom, $dateTo);
        } elseif (Schema::hasColumn('video_progresses', 'created_at')) {
            $this->applyDateRange($query, 'created_at', $dateFrom, $dateTo);
        }

        return $query->pluck('metric', 'user_id')->toArray();
    }

    /**
     * @param Builder $query
     * @param string $column
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @return void
     */
    protected function applyDateRange(Builder $query, string $column, ?Carbon $dateFrom, ?Carbon $dateTo): void
    {
        if ($dateFrom) {
            $query->where($column, '>=', $dateFrom->copy()->startOfDay());
        }

        if ($dateTo) {
            $query->where($column, '<=', $dateTo->copy()->endOfDay());
        }
    }
}
