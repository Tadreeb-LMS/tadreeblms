<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $export_urls = [
            'user/export-internal-attendence-report-as-csv',
            'user/exportCourseAsCsv', 
            'user/export-trainees-as-csv',
            'user/course-assignment-report-as-csv',
            'employee/sample',
        ];

        if (in_array($request->path(), $export_urls)) {
            return $next($request); // Skip CORS for this route
        }
        
        return $next($request)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
