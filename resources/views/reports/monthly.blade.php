@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
        <div>
            <h1 class="mb-1 text-primary fw-bold">Dashboard</h1>
            <div class="text-muted small">Your class statistics, salary, and fixed students at a glance</div>
        </div>
        <div>
            <form method="GET" action="{{ route('reports.monthly') }}" class="d-inline">
                <label for="month" class="me-2 small text-muted">Month:</label>
                <select name="month" id="month" class="form-select form-select-sm glass-card d-inline-block w-auto" onchange="this.form.submit()">
                    @foreach($months as $m)
                        <option value="{{ $m }}" @if($month == $m) selected @endif>{{ \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y') }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="app-card glass-card p-3 text-center h-100">
                <div class="fs-4 fw-bold">{{ $allTimePresentDays }}</div>
                <div class="small text-muted">Present days</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="app-card glass-card p-3 text-center h-100">
                <div class="fs-4 fw-bold">{{ $totalAbsent }}</div>
                <div class="small text-muted">Absent days</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="app-card glass-card p-3 text-center h-100">
                <div class="fs-4 fw-bold">{{ $totalClasses }}</div>
                <div class="small text-muted">Monthly Classes</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="app-card glass-card p-3 text-center h-100">
                <div class="fs-4 fw-bold">{{ $allTimeClasses }}</div>
                <div class="small text-muted">All time classes</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="app-card glass-card p-3 text-center h-100">
                <div class="fs-4 fw-bold">{{ number_format($totalSalary, 0) }}</div>
                <div class="small text-muted">Monthly Salary</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="app-card glass-card p-3 text-center h-100">
                <div class="fs-4 fw-bold">{{ number_format($allTimeSalary, 0) }}</div>
                <div class="small text-muted">Total Salary</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-9">
            <div class="app-card shadow-sm rounded-4 border-0 p-4 bg-white glass-card mb-4">
                <div class="table-responsive">
                    <table class="table table-striped align-middle modern-table mb-0">
                        <thead class="table-primary text-dark fs-6">
                            <tr>
                                <th class="py-3 px-3" style="width: 120px;">Date</th>
                                <th class="py-3 px-3" style="width: 80px;">Day</th>
                                <th class="py-3 px-3" style="width: 100px;">Classes</th>
                                <th class="py-3 px-3" style="width: 110px;">Absent?</th>
                                <th class="py-3 px-3">Daily Salary (PHP)</th>
                                <th class="py-3 px-3" style="width: 180px;"></th>
                            </tr>
                        </thead>
                        <tbody class="fs-6">
                            @foreach($days as $d)
                                <tr>
                                    <td class="py-2 px-3">{{ $d['date'] }}</td>
                                    <td class="py-2 px-3">{{ $d['dow'] }}</td>
                                    <td class="py-2 px-3">{{ $d['classes_count'] }}</td>
                                    <td class="py-2 px-3">{{ $d['absent'] ? 'Yes' : 'No' }}</td>
                                    <td class="py-2 px-3">{{ number_format($d['daily_salary'], 2) }}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="app-card shadow-sm rounded-4 border-0 p-4 bg-white glass-card">
                <h5 class="mb-3 text-primary">Classes per Day (Bar Chart)</h5>
                <canvas id="classesChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="app-card shadow-sm rounded-4 border-0 p-4 glass-card h-100">
                <h4 class="mb-3 text-primary">Fixed Students</h4>
                @if(isset($fixedStudents) && count($fixedStudents) > 0)
                    <ul class="list-group list-group-flush bg-transparent">
                        @foreach($fixedStudents as $student)
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 ps-0 pe-0">
                                <span class="fw-semibold">{{ $student['name'] }}</span>
                                <span class="text-muted small">
                                    @if($student['age']) Age: {{ $student['age'] }} @else Age: N/A @endif<br>
                                    <span class="text-primary">Booked: {{ $student['count'] }}x</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-2 text-muted">Total fixed students: <strong>{{ count($fixedStudents) }}</strong></div>
                @else
                    <div class="text-muted">No fixed students for this month.</div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .modern-table th, .modern-table td {
            border: none !important;
        }
        .modern-table thead {
            background: linear-gradient(90deg, #e3f0ff 0%, #f8f9fa 100%);
        }
        .modern-table tbody tr {
            transition: background 0.2s;
        }
        .modern-table tbody tr:hover {
            background: #f1f7ff;
        }
        .modern-table tfoot {
            background: #f8f9fa;
        }
        .app-card {
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }
        .glass-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.55) 0%, rgba(255,255,255,0.35) 100%) !important;
            border: 1px solid rgba(15,23,42,0.10);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .fw-bold { font-weight: 700 !important; }
        .list-group-item.bg-transparent { background: transparent !important; }
    </style>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('classesChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json(array_column($days, 'date')),
                    datasets: [{
                        label: 'Classes per Day',
                        data: @json(array_column($days, 'classes_count')),
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderRadius: 6,
                        maxBarThickness: 32
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: { display: false }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#333', font: { size: 13 } } },
                        y: { beginAtZero: true, grid: { color: '#e3e3e3' }, ticks: { color: '#333', font: { size: 13 } } }
                    }
                }
            });
        });
    </script>
</div>
@endsection

