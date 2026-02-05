@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
        <div>
            <h1 class="mb-1 text-white">Daily Schedule</h1>
            <div class="text-white-50 small">2:00 PM–11:30 PM • 30-minute slots</div>
        </div>
        <div class="text-white-50 small">
            Status:
            @if($isLocked)
                <span class="badge text-bg-light">Locked</span>
            @else
                <span class="badge text-bg-warning">Editing</span>
            @endif
        </div>
    </div>

    <div class="app-card mb-3">
        <div class="app-card-header p-3">
            <form method="GET" action="{{ route('schedule.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-auto">
                    <label class="form-label mb-1 text-muted small">Select date</label>
                    <input type="date" name="date" value="{{ $date }}" class="form-control">
                </div>
                <div class="col-12 col-md-auto">
                    <button class="btn btn-outline-secondary w-100">View Day</button>
                </div>
                <div class="col-12 col-md-auto ms-md-auto">
                    <div class="small text-muted">
                        Tip: leave a slot blank to clear it.
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="app-card">
        <div class="p-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div>
                <div class="fw-semibold">Schedule for <span class="text-primary">{{ $date }}</span></div>
                <div class="small text-muted">Time is shown in 12-hour format with 24-hour beside it.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($isLocked)
                    <form method="POST" action="{{ route('schedule.unlock') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button class="btn btn-outline-secondary px-4">Update</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('schedule.save') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button class="btn btn-gradient px-4">Save</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- When locked, show a notice and disable editing --}}
        @if($isLocked)
            <div class="px-3 pb-2">
                <div class="alert alert-info mb-0">
                    This schedule is locked. Click <strong>Update</strong> to enable editing.
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('schedule.save') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm bg-white mb-0">
                    <thead class="table-light">
                <tr>
                    <th style="width: 150px;">Time</th>
                    <th style="width: 220px;">Student Name</th>
                    <th style="width: 80px;">Age</th>
                    <th>Notes</th>
                </tr>
                    </thead>
                    <tbody>
            @foreach($period as $time)
                @php
                    $slotKey = $time->format('H:i:s');
                    $lesson = $lessons[$slotKey] ?? null;
                @endphp
                <tr>
                    <td>
                        {{ $time->format('g:i A') }}
                        <br>
                        <small class="text-muted">{{ $time->format('H:i') }}</small>
                    </td>
                    <td>
                        <input type="text"
                               name="slots[{{ $slotKey }}][student_name]"
                               class="form-control form-control-sm"
                               @disabled($isLocked)
                               value="{{ old("slots.{$slotKey}.student_name", $lesson->student_name ?? '') }}">
                    </td>
                    <td>
                        <input type="text"
                               name="slots[{{ $slotKey }}][age]"
                               class="form-control form-control-sm"
                               @disabled($isLocked)
                               value="{{ old("slots.{$slotKey}.age", $lesson->age ?? '') }}">
                    </td>
                    <td>
                        <input type="text"
                               name="slots[{{ $slotKey }}][notes]"
                               class="form-control form-control-sm"
                               @disabled($isLocked)
                               value="{{ old("slots.{$slotKey}.notes", $lesson->notes ?? '') }}">
                    </td>
                </tr>
            @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
@endsection

