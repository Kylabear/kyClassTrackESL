    @if(session('status') && request('slots'))
        <div class="alert alert-warning mt-2">
            <strong>Debug:</strong> Slots POSTed:<br>
            <pre>{{ print_r(request('slots'), true) }}</pre>
        </div>
    @endif
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 px-md-5">
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
                <div class="col-12 col-md-auto ms-auto text-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#autoschedModal">
                        AutoSched
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal moved outside main container below -->
<!-- AutoSched Modal moved outside main container for proper overlay -->
<div class="modal fade" id="autoschedModal" tabindex="-1" aria-labelledby="autoschedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background: rgba(255,255,255,0.96); border-radius: 22px; box-shadow: 0 8px 32px 0 rgba(31,38,135,0.18), 0 1.5px 8px 0 rgba(37,99,235,0.10); z-index: 2147483647;">
            <form id="autoschedForm" method="POST" action="{{ route('schedule.autosched') }}">
                <input type="hidden" name="auto_sched" value="1">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="autoschedModalLabel">AutoSched Batch Scheduling</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="auto_sched_student_name" class="form-label">Student Name</label>
                        <input type="text" class="form-control" id="auto_sched_student_name" name="auto_sched_student_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="auto_sched_age" class="form-label">Age</label>
                        <input type="number" class="form-control" id="auto_sched_age" name="auto_sched_age" min="1" required>
                    </div>
                    <div class="mb-3 row">
                        <div class="col">
                            <label for="auto_sched_from_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="auto_sched_from_date" name="from_date" required>
                        </div>
                        <div class="col">
                            <label for="auto_sched_to_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="auto_sched_to_date" name="to_date" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col">
                            <label for="auto_sched_from_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="auto_sched_from_time" name="from_time" required>
                        </div>
                        <div class="col">
                            <label for="auto_sched_to_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="auto_sched_to_time" name="to_time" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


    @if(session('status'))
        <div id="toast-bar" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 250px; background: #333; color: #fff; padding: 16px 24px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 1rem; opacity: 0.95;">
            {{ session('status') }}
        </div>
        <script>
            setTimeout(function() {
                var toast = document.getElementById('toast-bar');
                if (toast) toast.style.display = 'none';
            }, 3500);
        </script>
    @endif

    <div class="app-card">
        <div class="p-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div>
                <div class="fw-semibold">Schedule for <span class="text-primary">{{ $date }}</span></div>
                <div class="small text-muted">Time is shown in 12-hour format with 24-hour beside it.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @php
                    $hasAnySchedule = $lessons->count() > 0;
                @endphp
                {{-- Only show Update button if locked and has a schedule --}}
                @if($isLocked && $hasAnySchedule)
                    <form method="POST" action="{{ route('schedule.unlock') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button class="btn btn-outline-secondary px-4">Update</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Show the form if NOT locked, OR if locked but has NO schedule (so user can add schedule) --}}
        @if(!$isLocked || ($isLocked && !$hasAnySchedule))
            <form method="POST" action="{{ route('schedule.save') }}" style="padding-left:2.5rem;padding-right:2.5rem;">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-sm mb-0" style="background:transparent;">
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
                                   value="{{ old("slots.{$slotKey}.student_name", ($lesson && $lesson->student_name) ? $lesson->student_name : 'Breaktime') }}"
                                   @if(empty($lesson) || empty($lesson->student_name)) style="color:#aaa;" @endif
                                   onfocus="if(this.value==='Breaktime'){this.value='';this.style.color='#212529';}"
                                   onblur="if(this.value===''){this.value='Breaktime';this.style.color='#aaa';}">
                        </td>
                        <td>
                            <input type="text"
                                   name="slots[{{ $slotKey }}][age]"
                                   class="form-control form-control-sm"
                                   value="{{ old("slots.{$slotKey}.age", $lesson->age ?? '') }}">
                        </td>
                        <td>
                            <input type="text"
                                   name="slots[{{ $slotKey }}][notes]"
                                   class="form-control form-control-sm"
                                   value="{{ old("slots.{$slotKey}.notes", $lesson->notes ?? '') }}">
                        </td>
                    </tr>
                @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-end">
                    <button class="btn btn-gradient px-4">Save</button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection

