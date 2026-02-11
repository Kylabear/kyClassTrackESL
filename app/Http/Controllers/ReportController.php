<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
    * Show monthly summary: classes/day, absent days, salary.
    */
    public function monthly(Request $request)
    {
        // Generate months list for selectors (Jan 2026 - Jun 2026)
        $months = [];
        $startMonth = \Carbon\Carbon::create(2026, 1, 1);
        $endMonth = \Carbon\Carbon::create(2026, 6, 1);
    while ($startMonth <= $endMonth) {
        $months[] = $startMonth->format('Y-m');
        $startMonth->addMonth();
    }

    $userId = 1;
        $firstDay = Carbon::create(2026, 1, 16);
        $monthRaw = $request->input('month', now()->format('Y-m'));
        // Always extract only the year and month (Y-m) from input
        if (preg_match('/^(\d{4})-(\d{2})/', $monthRaw, $matches)) {
            $month = $matches[1] . '-' . $matches[2];
        } else {
            $month = now()->format('Y-m');
        }

        // Prevent selecting months before January 2026
        $selectedMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        if ($selectedMonth->lessThan($firstDay->copy()->startOfMonth())) {
            $selectedMonth = $firstDay->copy()->startOfMonth();
            $month = $selectedMonth->format('Y-m');
        }

        $start = $selectedMonth;
        $end   = (clone $start)->endOfMonth();


        // Fixed students: 5 or more bookings, exclude 'Breaktime'
        $fixedStudentsRaw = Lesson::select('student_name', DB::raw('MAX(age) as age'), DB::raw('COUNT(*) as count'))
            ->where('user_id', $userId)
            ->whereNotNull('student_name')
            ->where('student_name', '!=', 'Breaktime')
            ->groupBy('student_name')
            ->havingRaw('COUNT(*) >= 5')
            ->get();

        $fixedStudents = $fixedStudentsRaw->map(function($row) {
            return [
                'name' => $row->student_name,
                'age' => $row->age,
                'count' => $row->count,
            ];
        })
        ->sortByDesc('count')
        ->values()
        ->toArray();

        // Other students: 1-4 bookings, exclude 'Breaktime'
        $otherStudentsRaw = Lesson::select('student_name', DB::raw('MAX(age) as age'), DB::raw('COUNT(*) as count'))
            ->where('user_id', $userId)
            ->whereNotNull('student_name')
            ->where('student_name', '!=', 'Breaktime')
            ->groupBy('student_name')
            ->havingRaw('COUNT(*) >= 1 AND COUNT(*) <= 4')
            ->get();

        $otherStudents = $otherStudentsRaw->map(function($row) {
            return [
                'name' => $row->student_name,
                'age' => $row->age,
                'count' => $row->count,
            ];
        })
        ->sortByDesc('count')
        ->values()
        ->toArray();

        $classesPerDay = Lesson::select(
                DB::raw('DATE(date) as day'),
                DB::raw('COUNT(*) as classes_count')
            )
            ->where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->where('student_name', '!=', 'Breaktime')
            ->groupBy('day')
            ->pluck('classes_count', 'day');

        $days = [];
        $today = Carbon::today();
        foreach (CarbonPeriod::create($start, $end) as $day) {
            // Only include days from Jan 16, 2026 onward
            if ($day->lessThan($firstDay)) continue;
            $dateStr = $day->toDateString();
            $count   = $classesPerDay[$dateStr] ?? 0;
            $isFuture = $day->greaterThan($today);
            $days[] = [
                'date'          => $dateStr,
                'dow'           => $day->format('D'),
                'classes_count' => $count,
                'daily_salary'  => $count * 60,
                'absent'        => $isFuture ? null : ($count === 0),
            ];
        }

        $totalClasses = array_sum(array_column($days, 'classes_count'));
        $totalAbsent  = collect($days)->where('absent', true)->count();
        $totalSalary  = $totalClasses * 60;

        // All-time summary (from firstDay to today)
        $allTimeEnd = now()->toDateString();

        // All-time present days: count unique days with at least one class
        $allTimePresentDays = Lesson::where('user_id', $userId)
            ->whereBetween('date', [$firstDay->toDateString(), $allTimeEnd])
            ->where('student_name', '!=', 'Breaktime')
            ->distinct('date')
            ->count('date');

        $allTimeClasses = Lesson::where('user_id', $userId)
            ->whereBetween('date', [$firstDay->toDateString(), $allTimeEnd])
            ->where('student_name', '!=', 'Breaktime')
            ->count();
        $allTimeSalary = $allTimeClasses * 60;

        return view('reports.monthly', compact(
            'month',
            'days',
            'totalClasses',
            'totalAbsent',
            'totalSalary',
            'allTimeClasses',
            'allTimeSalary',
            'allTimePresentDays',
            'fixedStudents',
            'otherStudents',
            'months'
        ));
    }
}

