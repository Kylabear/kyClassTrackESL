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

        $scheduleDay = ScheduleDay::firstOrCreate(
            ['user_id' => $userId, 'date' => $date],
            ['is_locked' => false, 'locked_at' => null]
        );

        if ($scheduleDay->is_locked) {
            return redirect()
                ->route('schedule.index', ['date' => $date])
                ->with('status', 'This day is locked. Click Update to edit.');
        }

        $slots = $request->input('slots', []); // [ '14:00:00' => [...], ... ]
        // Debug output
        
        Log::info('Schedule Save Request', [
            'date' => $date,
            'slots' => $slots,
        ]);

        $filledCount = 0;
        foreach ($slots as $time => $data) {
            // Always use H:i:s for start_time
            $startTime = date('H:i:s', strtotime($time));
            $hasData = filled($data['student_name'] ?? null)
                || filled($data['age'] ?? null)
                || filled($data['notes'] ?? null);

            if (! $hasData) {
                Lesson::where('user_id', $userId)
                    ->whereDate('date', $date)
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
                    'date'        => $date,
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

        Log::info('Schedule Save Filled Count', [
            'filledCount' => $filledCount,
        ]);

        if ($filledCount > 0) {
            $scheduleDay->forceFill([
                'is_locked' => true,
                'locked_at' => now(),
            ])->save();
            $status = 'Schedule saved and locked.';
        } else {
            $scheduleDay->forceFill([
                'is_locked' => false,
                'locked_at' => null,
            ])->save();
            $status = 'No schedule placed. Day remains editable.';
        }

        return redirect()
            ->route('schedule.index', ['date' => $date])
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


