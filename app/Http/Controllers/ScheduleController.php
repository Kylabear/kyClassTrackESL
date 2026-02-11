<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\ScheduleDay;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    /**
     * Handle AutoSched batch scheduling form submission.
     */
    public function autosched(Request $request)
    {
        $request->validate([
            'auto_sched_student_name' => 'required|string',
            'auto_sched_age' => 'required|integer',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'from_time' => 'required',
            'to_time' => 'required',
        ]);

        $userId = 1; // Or get from auth
        $studentName = $request->input('auto_sched_student_name');
        $age = $request->input('auto_sched_age');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $fromTime = $request->input('from_time');
        $toTime = $request->input('to_time');

        $period = \Carbon\CarbonPeriod::create($fromDate, $toDate);
        foreach ($period as $date) {
            $targetDate = $date->toDateString();
            $scheduleDay = \App\Models\ScheduleDay::firstOrCreate(
                ['user_id' => $userId, 'date' => $targetDate],
                ['is_locked' => false, 'locked_at' => null]
            );
            if ($scheduleDay->is_locked) continue;

            // 30-min slots from 14:00 to 23:30
            $slotTimes = [];
            $start = strtotime('14:00');
            $end = strtotime('23:30');
            for ($t = $start; $t <= $end; $t += 30 * 60) {
                $slot = date('H:i:s', $t);
                if ($slot >= $fromTime && $slot <= $toTime) {
                    $slotTimes[] = $slot;
                }
            }
            foreach ($slotTimes as $slotTime) {
                $count = \App\Models\Lesson::where('user_id', $userId)
                    ->where('student_name', $studentName)
                    ->count();
                $isFixed = $count >= 4;
                \App\Models\Lesson::updateOrCreate(
                    [
                        'user_id'     => $userId,
                        'date'        => $targetDate,
                        'start_time'  => $slotTime,
                    ],
                    [
                        'student_name' => $studentName,
                        'age'          => $age,
                        'notes'        => null,
                        'is_fixed_student' => $isFixed,
                    ]
                );
            }
        }

        return redirect()->route('schedule.index', ['date' => $fromDate])
            ->with('status', 'AutoSched batch schedule saved!');
    }
    /**
     * Show the weekly schedule (upcoming and past).
     */
    public function weekly(Request $request)
    {
        $userId = 1;
        $today = now()->toDateString();
        $firstDay = '2026-01-16';

        // Use selected date if provided, else default to today
        $selectedDate = $request->input('date', $today);
        // Clamp selected date to not be before Jan 16, 2026
        if ($selectedDate < $firstDay) {
            $selectedDate = $firstDay;
        }
        $startOfWeek = \Carbon\Carbon::parse($selectedDate)->startOfWeek()->toDateString();
        $endOfWeek = \Carbon\Carbon::parse($selectedDate)->endOfWeek()->toDateString();

        // Clamp startOfWeek to not be before Jan 16, 2026
        if ($startOfWeek < $firstDay) {
            $startOfWeek = $firstDay;
        }

        $lessons = Lesson::where('user_id', $userId)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $grouped = $lessons->groupBy('date');

        return view('schedule.weekly', [
            'grouped' => $grouped,
            'today' => $today,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
        ]);
    }

    /**
     * Show upcoming schedules (future lessons only).
     */
    public function upcoming(Request $request)
    {
        $userId = 1;
        $today = now()->toDateString();
        $lessons = Lesson::where('user_id', $userId)
            ->where('date', '>=', $today)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
        return view('schedule.upcoming', ['lessons' => $lessons]);
    }

    /**
     * Show past schedules (past lessons only).
     */
    public function past(Request $request)
    {
        $userId = 1;
        $today = now()->toDateString();
        $lessons = Lesson::where('user_id', $userId)
            ->where('date', '<', $today)
            ->orderByDesc('date')
            ->orderBy('start_time')
            ->get();
        return view('schedule.past', ['lessons' => $lessons]);
    }

    /**
     * Delete a lesson (schedule entry).
     */
    public function delete(Request $request, $id)
    {
        Lesson::where('id', $id)->delete();
        return back()->with('status', 'Schedule entry deleted.');
    }

    /**
     * Show the daily schedule.
     */
    public function index(Request $request)
    {
        // In a real Laravel app, you would use $request->user()->id.
        // For now we assume a single teacher with id = 1.
        $userId = 1;

        $date = $request->input('date', now()->toDateString());

        $scheduleDay = ScheduleDay::firstOrCreate(
            ['user_id' => $userId, 'date' => $date],
            ['is_locked' => false, 'locked_at' => null]
        );

        $isLocked = (bool) $scheduleDay->is_locked;

        // Create 30-minute slots from 14:00 (2 PM) to 23:30 (11:30 PM)
        $period = CarbonPeriod::create('14:00', '30 minutes', '23:30');

        $lessons = Lesson::where('user_id', $userId)
            ->whereDate('date', $date)
            ->get()
            // Key strictly by the raw DB time string (HH:MM:SS)
            ->keyBy(fn (Lesson $lesson) => $lesson->getRawOriginal('start_time'));

        return view('schedule.index', compact('date', 'period', 'lessons', 'isLocked'));
    }

    /**
    * Save the daily schedule and lock it.
    */
    public function save(Request $request)
    {
        $userId = 1;
        $date = $request->input('date');
        $slots = $request->input('slots', []); // [ '14:00:00' => [...], ... ]

        // Check for batch scheduling (AutoSched)
        $autoSched = $request->input('auto_sched', false);
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $fromTime = $request->input('from_time');
        $toTime = $request->input('to_time');
        $autoStudent = $request->input('auto_sched_student_name');
        $autoAge = $request->input('auto_sched_age');

        $dates = collect([$date]);
        if ($autoSched && $fromDate && $toDate) {
            try {
                $period = \Carbon\CarbonPeriod::create($fromDate, $toDate);
                $dates = collect();
                foreach ($period as $d) {
                    $dates->push($d->toDateString());
                }
            } catch (\Exception $e) {
                $dates = collect([$date]);
            }
        }

        $filledCount = 0;
        foreach ($dates as $targetDate) {
            $scheduleDay = ScheduleDay::firstOrCreate(
                ['user_id' => $userId, 'date' => $targetDate],
                ['is_locked' => false, 'locked_at' => null]
            );

            if ($scheduleDay->is_locked) {
                continue;
            }

            // If batch scheduling, ignore slots and use time range
            if ($autoSched && $fromTime && $toTime && $autoStudent) {
                // 30-min slots from 14:00 to 23:30
                $slotTimes = [];
                $start = strtotime('14:00');
                $end = strtotime('23:30');
                for ($t = $start; $t <= $end; $t += 30 * 60) {
                    $slot = date('H:i:s', $t);
                    if ($slot >= $fromTime && $slot <= $toTime) {
                        $slotTimes[] = $slot;
                    }
                }
                foreach ($slotTimes as $slotTime) {
                    $filledCount++;
                    $isFixed = false;
                    $count = Lesson::where('user_id', $userId)
                        ->where('student_name', $autoStudent)
                        ->count();
                    $isFixed = $count >= 4;
                    Lesson::updateOrCreate(
                        [
                            'user_id'     => $userId,
                            'date'        => $targetDate,
                            'start_time'  => $slotTime,
                        ],
                        [
                            'student_name' => $autoStudent,
                            'age'          => $autoAge,
                            'notes'        => null,
                            'is_fixed_student' => $isFixed,
                        ]
                    );
                }
            } else {
                // Manual (single day) scheduling
                foreach ($slots as $time => $data) {
                    $startTime = date('H:i:s', strtotime($time));
                    $hasData = filled($data['student_name'] ?? null)
                        || filled($data['age'] ?? null)
                        || filled($data['notes'] ?? null);

                    if (! $hasData) {
                        Lesson::where('user_id', $userId)
                            ->whereDate('date', $targetDate)
                            ->where('start_time', $startTime)
                            ->delete();
                        continue;
                    }

                    $filledCount++;
                    $studentName = $data['student_name'] ?? null;
                    $isFixed = false;
                    if ($studentName) {
                        $count = Lesson::where('user_id', $userId)
                            ->where('student_name', $studentName)
                            ->count();
                        $isFixed = $count >= 4; // This will be the 5th booking
                    }

                    Lesson::updateOrCreate(
                        [
                            'user_id'     => $userId,
                            'date'        => $targetDate,
                            'start_time'  => $startTime,
                        ],
                        [
                            'student_name' => $studentName,
                            'age'          => $data['age'] ?? null,
                            'notes'        => $data['notes'] ?? null,
                            'is_fixed_student' => $isFixed,
                        ]
                    );
                }
            }

            if ($filledCount > 0) {
                $scheduleDay->forceFill([
                    'is_locked' => true,
                    'locked_at' => now(),
                ])->save();
            } else {
                $scheduleDay->forceFill([
                    'is_locked' => false,
                    'locked_at' => null,
                ])->save();
            }
        }

        $status = $filledCount > 0
            ? 'Schedule saved and locked for selected days.'
            : 'No schedule placed. Days remain editable.';

        // Redirect to the first date in the range
        return redirect()
            ->route('schedule.index', ['date' => $dates->first()])
            ->with('status', $status);
    }
    /**
     * Unlock a day's schedule so it can be edited.
     */
    public function unlock(Request $request)
    {
        $userId = 1;
        $date = $request->input('date');

        $scheduleDay = ScheduleDay::firstOrCreate(
            ['user_id' => $userId, 'date' => $date],
            ['is_locked' => false, 'locked_at' => null]
        );

        $scheduleDay->forceFill([
            'is_locked' => false,
            'locked_at' => null,
        ])->save();

        return redirect()
            ->route('schedule.index', ['date' => $date])
            ->with('status', 'Editing enabled. Make your changes, then click Save to lock again.');
    }
}


