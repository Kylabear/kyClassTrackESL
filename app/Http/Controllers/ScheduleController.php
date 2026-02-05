<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\ScheduleDay;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
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

        foreach ($slots as $time => $data) {
            $hasData = filled($data['student_name'] ?? null)
                || filled($data['age'] ?? null)
                || filled($data['notes'] ?? null);

            if (! $hasData) {
                Lesson::where('user_id', $userId)
                    ->whereDate('date', $date)
                    ->where('start_time', $time)
                    ->delete();
                continue;
            }

            Lesson::updateOrCreate(
                [
                    'user_id'     => $userId,
                    'date'        => $date,
                    'start_time'  => $time,
                ],
                [
                    'student_name' => $data['student_name'] ?? null,
                    'age'          => $data['age'] ?? null,
                    'notes'        => $data['notes'] ?? null,
                ]
            );
        }

        $scheduleDay->forceFill([
            'is_locked' => true,
            'locked_at' => now(),
        ])->save();

        return redirect()
            ->route('schedule.index', ['date' => $date])
            ->with('status', 'Schedule saved and locked.');
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

