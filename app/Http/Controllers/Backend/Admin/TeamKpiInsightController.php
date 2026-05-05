<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Department;
use App\Models\Kpi;
use App\Services\Kpi\TeamKpiInsightService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamKpiInsightController extends Controller
{
    /**
     * @var TeamKpiInsightService
     */
    protected $insightService;

    public function __construct(TeamKpiInsightService $insightService)
    {
        $this->insightService = $insightService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        if (!Gate::allows('kpi_access')) {
            return abort(401);
        }

        $request->validate([
            'team_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $teamsQuery = Department::query()->select('id', 'title', 'user_id')->orderBy('title');

        $isManagerOnly = $user->hasRole('manager') && !$user->hasAnyRole(['administrator', 'teacher']);
        if ($isManagerOnly) {
            $teamsQuery->where('user_id', $user->id);
        }

        $teams = $teamsQuery->get();

        $selectedTeamId = (int) $request->input('team_id', 0);
        if ($selectedTeamId <= 0 && $teams->isNotEmpty()) {
            $selectedTeamId = (int) $teams->first()->id;
        }

        if ($selectedTeamId > 0 && !$teams->contains('id', $selectedTeamId)) {
            return abort(403);
        }

        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->input('date_from'))->startOfDay() : null;
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->input('date_to'))->endOfDay() : null;

        $members = collect();
        $memberDirectory = [];
        $insights = [
            'kpi_summaries' => [],
            'top_performers' => [],
            'bottom_performers' => [],
            'team_score_average' => null,
            'team_member_count' => 0,
            'evaluated_member_count' => 0,
        ];

        if ($selectedTeamId > 0) {
            $members = User::query()
                ->select('users.id', 'users.first_name', 'users.last_name', 'users.email')
                ->join('employee_profiles', 'employee_profiles.user_id', '=', 'users.id')
                ->where('employee_profiles.department', $selectedTeamId)
                ->orderBy('users.first_name')
                ->orderBy('users.last_name')
                ->get();

            $memberDirectory = $members->mapWithKeys(function ($member) {
                $fullName = trim((string) $member->first_name . ' ' . (string) $member->last_name);
                return [(int) $member->id => $fullName !== '' ? $fullName : ((string) $member->email ?: ('User #' . $member->id))];
            })->all();

            $kpis = Kpi::query()
                ->where('is_active', true)
                ->with('categories:id,name', 'courses:id')
                ->orderBy('name')
                ->get();

            $insights = $this->insightService->buildInsights(
                $kpis,
                $members->pluck('id')->all(),
                $memberDirectory,
                $dateFrom,
                $dateTo
            );
        }

        return view('backend.kpis.team_insights', [
            'teams' => $teams,
            'selectedTeamId' => $selectedTeamId,
            'dateFrom' => $request->input('date_from'),
            'dateTo' => $request->input('date_to'),
            'members' => $members,
            'insights' => $insights,
        ]);
    }
}
