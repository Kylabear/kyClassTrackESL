@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
        <div>
            <h1 class="mb-1 text-white">Weekly Schedule</h1>
            <div class="text-white-50 small">Week: {{ $startOfWeek }} to {{ $endOfWeek }}</div>
        </div>
        <form method="GET" action="{{ route('schedule.weekly') }}" class="d-flex align-items-center gap-2">
            <label for="week_date" class="text-white-50 small mb-0">Select date</label>
            <input type="date" id="week_date" name="date" value="{{ request('date', $today) }}" class="form-control form-control-sm">
            <button class="btn btn-outline-light btn-sm">Go</button>
        </form>
    </div>
    @if($grouped->isEmpty())
        <div class="alert alert-info">No lessons scheduled this week.</div>
    @else
        @foreach($grouped as $date => $lessons)
            <div class="app-card mb-4">
                <div class="app-card-header p-3">
                    <strong>{{ $date }} @if($date === $today)<span class="badge bg-primary">Today</span>@endif</strong>
                </div>
                <div class="p-3">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Student Name</th>
                                <th>Age</th>
                                <th>Notes</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($lessons as $lesson)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($lesson->start_time)->format('g:i A') }}</td>
                                <td>{{ $lesson->student_name }}</td>
                                <td>{{ $lesson->age }}</td>
                                <td>{{ $lesson->notes }}</td>
                                <td>
                                    @if($lesson->is_fixed_student)
                                        <span class="badge bg-success">Fixed</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('schedule.delete', $lesson->id) }}" onsubmit="return confirm('Delete this schedule entry?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
