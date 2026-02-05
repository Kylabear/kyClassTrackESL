@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
        <div>
            <h1 class="mb-1 text-white">Monthly Report</h1>
            <div class="text-white-50 small">Classes, absences, and salary totals</div>
        </div>
    </div>

    <div class="app-card mb-3">
        <div class="app-card-header p-3">
            <form method="GET" action="{{ route('reports.monthly') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-auto">
                    <label class="form-label mb-1 text-muted small">Select month</label>
                    <input type="month" name="month" value="{{ $month }}" class="form-control">
                </div>
                <div class="col-12 col-md-auto">
                    <button class="btn btn-outline-secondary w-100">View Month</button>
                </div>
                <div class="col-12 col-md-auto ms-md-auto">
                    <div class="small text-muted">
                        Salary is computed as <strong>classes × 60 PHP</strong>.
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="app-card">
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-sm bg-white mb-0">
                <thead class="table-light">
            <tr>
                <th style="width: 120px;">Date</th>
                <th style="width: 80px;">Day</th>
                <th style="width: 100px;">Classes</th>
                <th style="width: 90px;">Absent?</th>
                <th>Daily Salary (PHP)</th>
            </tr>
                </thead>
                <tbody>
        @foreach($days as $d)
            <tr>
                <td>{{ $d['date'] }}</td>
                <td>{{ $d['dow'] }}</td>
                <td>{{ $d['classes_count'] }}</td>
                <td>{{ $d['absent'] ? 'Yes' : 'No' }}</td>
                <td>{{ number_format($d['daily_salary'], 2) }}</td>
            </tr>
        @endforeach
                </tbody>
                <tfoot class="table-light">
            <tr>
                <th colspan="2">Totals</th>
                <th>{{ $totalClasses }} classes</th>
                <th>{{ $totalAbsent }} days absent</th>
                <th>{{ number_format($totalSalary, 2) }} PHP</th>
            </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

