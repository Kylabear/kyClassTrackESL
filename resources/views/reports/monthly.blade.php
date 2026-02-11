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

    <!-- Summary Cards Row -->
    <div class="row g-3 mb-4 align-items-stretch">
        <div class="col-6 col-sm-4 col-md-2 mb-3 mb-md-0 d-flex align-items-stretch">
            <div class="app-card glass-card p-3 text-center w-100 d-flex flex-column justify-content-center" style="min-height: 120px;">
                <div class="fs-4 fw-bold">{{ $allTimePresentDays }}</div>
                <div class="small text-muted">Present days</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2 mb-3 mb-md-0">
            <div class="app-card glass-card p-3 text-center h-100">
                <div class="fs-4 fw-bold">{{ $totalAbsent }}</div>
                <div class="small text-muted">Absent days</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2 mb-3 mb-md-0">
            <div class="app-card glass-card p-3 text-center h-100">
                <div class="fs-4 fw-bold unlockable" data-value="{{ $totalClasses }}" data-label="Monthly Classes">*****</div>
                <div class="small text-muted">Monthly Classes</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2 mb-3 mb-md-0">
            <div class="app-card glass-card p-3 text-center h-100">
                <div class="fs-4 fw-bold unlockable" data-value="{{ $allTimeClasses }}" data-label="All time classes">*****</div>
                <div class="small text-muted">All time classes</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2 mb-3 mb-md-0">
            <div class="app-card glass-card p-3 text-center h-100">
                <div class="fs-4 fw-bold unlockable" data-value="{{ number_format($totalSalary, 0) }}" data-label="Monthly Salary">*****</div>
                <div class="small text-muted">Monthly Salary</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2 mb-3 mb-md-0">
            <div class="app-card glass-card p-3 text-center h-100">
                <div class="fs-4 fw-bold unlockable" data-value="{{ number_format($allTimeSalary, 0) }}" data-label="Total Salary">*****</div>
                <div class="small text-muted">Total Salary</div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function showUnlockPrompt(el) {
            if (el.classList.contains('unlocked')) return;
            const label = el.getAttribute('data-label') || '';
            const pop = document.createElement('div');
            pop.style.position = 'fixed';
            pop.style.left = '50%';
            pop.style.top = '30%';
            pop.style.transform = 'translate(-50%, -50%)';
            pop.style.background = 'rgba(20,20,20,0.98)';
            pop.style.color = '#fff';
            pop.style.padding = '32px 28px 24px 28px';
            pop.style.borderRadius = '16px';
            pop.style.boxShadow = '0 8px 32px rgba(0,0,0,0.18)';
            pop.style.zIndex = 9999;
            pop.innerHTML = `
                <div style="font-size:1.2rem;margin-bottom:12px;text-align:center;">Sus!Gusto Makita hahahha</div>
                <input type="password" id="unlockPass" class="form-control mb-3" placeholder="Enter password...">
                <button class="btn btn-primary w-100" id="unlockBtn">Unlock</button>
                <button class="btn btn-link w-100 mt-2" id="cancelBtn">Cancel</button>
            `;
            document.body.appendChild(pop);
            document.getElementById('unlockPass').focus();
            document.getElementById('unlockBtn').onclick = function() {
                const pass = document.getElementById('unlockPass').value;
                if (pass === 'secretngani2026') {
                    el.textContent = el.getAttribute('data-value');
                    el.classList.add('unlocked');
                    pop.remove();
                } else {
                    document.getElementById('unlockPass').value = '';
                    document.getElementById('unlockPass').placeholder = 'Wrong password!';
                }
            };
            document.getElementById('cancelBtn').onclick = function(e) {
                e.stopPropagation();
                pop.remove();
            };
        }
        document.querySelectorAll('.unlockable').forEach(function(el) {
            el.addEventListener('click', function(e) {
                showUnlockPrompt(el);
            });
            el.addEventListener('mouseenter', function(e) {
                if (!el.classList.contains('unlocked')) {
                    el.title = 'You want to know? hahahha';
                }
            });
        });
    });
    </script>
    </div>

    <!-- Table and Fixed Students Row -->
    <div class="row g-4 mb-4 align-items-start">
        <div class="col-12 col-lg-9 mb-4 mb-lg-0 d-flex flex-column h-100">
            <div class="app-card shadow-sm rounded-4 border-0 p-4 bg-white glass-card mb-4 h-100">
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
                                    <td class="py-2 px-3">
                                        @if(is_null($d['absent']))
                                            —
                                        @else
                                            {{ $d['absent'] ? 'Yes' : 'No' }}
                                        @endif
                                    </td>
                                    <td class="py-2 px-3">{{ number_format($d['daily_salary'], 2) }}</td>
                                    <td></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="app-card shadow-sm rounded-4 border-0 p-4 bg-white glass-card">
                <h5 class="mb-3 text-primary">Classes per Day</h5>
                <canvas id="classesChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-12 col-lg-3 d-flex flex-column h-100">
            <div class="app-card shadow-sm rounded-4 border-0 p-3 p-md-4 glass-card h-100">

                <h4 class="mb-1 fw-bold fixed-gradient-title">Fixed Students</h4>
                <div class="mb-3 text-center">
                    <span class="fw-bold fixed-gradient-count">Total fixed students: {{ count($fixedStudents ?? []) }}</span>
                </div>
                <style>
                    .fixed-gradient-title {
                        background: linear-gradient(90deg, #000 0%, #f00 50%, #00f 100%);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        text-fill-color: transparent;
                        font-weight: bold;
                        font-size: 2rem;
                    }
                    .fixed-gradient-count {
                        background: linear-gradient(90deg, #000 0%, #f00 50%, #00f 100%);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        text-fill-color: transparent;
                        font-weight: bold;
                        font-size: 1rem;
                    }
                </style>
                @if(isset($fixedStudents) && count($fixedStudents) > 0)
                    <ul class="list-group list-group-flush bg-transparent">
                        @foreach($fixedStudents as $student)
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 ps-0 pe-0">
                                <span class="fw-bold text-dark">{{ $student['name'] }}</span>
                                <span class="fw-bold text-dark small">
                                    @if($student['age']) Age: {{ $student['age'] }} @else Age: N/A @endif<br>
                                    <span class="fw-bold text-white">Booked: {{ $student['count'] }}x</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-muted">No fixed students for this month.</div>
                @endif

                <hr class="my-3">
                <button class="btn btn-outline-primary w-100 fw-bold mb-2" onclick="showStudentPopup('other')">
                    View Other Students
                </button>
                <div class="mt-2 fw-bold text-dark">Total other students: <span class="fw-bold text-dark">{{ isset($otherStudents) ? count($otherStudents) : 0 }}</span></div>


<!-- Student Popups (move outside card for stacking context) -->
@push('scripts')
<style>
#studentPopup { z-index: 2147483647 !important; }
#studentPopupContent { z-index: 2147483648 !important; }
</style>
<div id="studentPopup" style="display:none;align-items:center;justify-content:center;position:fixed;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:2147483647 !important;">
    <div id="studentPopupContent" class="glass-card" style="background: linear-gradient(135deg, rgba(255,255,255,0.75) 0%, rgba(255,255,255,0.45) 100%) !important; border: 1px solid rgba(15,23,42,0.10); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); padding:32px 24px 24px 24px; border-radius:16px; max-width:95vw; width:400px; max-height:80vh; overflow-y:auto; box-shadow:0 8px 32px rgba(0,0,0,0.18); position:relative; z-index:2147483648 !important;">
        <button onclick="closeStudentPopup()" style="position:absolute;top:12px;right:16px;font-size:1.5rem;background:none;border:none;">&times;</button>
        <h5 id="studentPopupTitle" class="mb-3 text-primary"></h5>
        <ul id="studentPopupList" class="list-group list-group-flush bg-transparent"></ul>
    </div>
</div>
<script>
function showStudentPopup(type) {
    var popup = document.getElementById('studentPopup');
    var list = document.getElementById('studentPopupList');
    var title = document.getElementById('studentPopupTitle');
    list.innerHTML = '';
    let students = [];
    let isFixed = (type === 'fixed');
    if (isFixed) {
        students = @json($fixedStudents);
        title.textContent = 'All Fixed Students';
    } else {
        students = @json($otherStudents);
        title.textContent = 'All Other Students';
    }
    students.forEach(function(student) {
        let li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 ps-0 pe-0';
        li.innerHTML = `<span class='fw-bold text-dark'>${student.name}</span><span class='fw-bold text-dark small'>${student.age ? 'Age: ' + student.age : 'Age: N/A'}<br><span class='fw-bold text-primary'>Booked: ${student.count}x</span></span>`;
        list.appendChild(li);
    });
    popup.style.display = 'flex';
    popup.focus();
}
function closeStudentPopup() {
    document.getElementById('studentPopup').style.display = 'none';
}
</script>
@endpush

                <hr class="my-3">
                <div class="fw-bold text-dark">Total students overall: <span class="fw-bold text-dark">{{ (isset($fixedStudents) ? count($fixedStudents) : 0) + (isset($otherStudents) ? count($otherStudents) : 0) }}</span></div>
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

        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .col-lg-9, .col-lg-3 { flex: 0 0 100%; max-width: 100%; }
            .app-card { margin-bottom: 1.5rem; }
        }
        @media (max-width: 767.98px) {
            .modern-table th, .modern-table td { font-size: 0.95rem; padding: 0.5rem 0.5rem; }
            .app-card, .glass-card { padding: 1rem !important; }
            .rounded-4 { border-radius: 1rem !important; }
        }
        @media (max-width: 575.98px) {
            .modern-table th, .modern-table td { font-size: 0.85rem; padding: 0.35rem 0.25rem; }
            .app-card, .glass-card { padding: 0.5rem !important; }
            .rounded-4 { border-radius: 0.5rem !important; }
            .p-4 { padding: 1rem !important; }
        }
        #studentPopupContent { width: 95vw; max-width: 400px; }
        @media (max-width: 575.98px) {
            #studentPopupContent { width: 99vw; max-width: 99vw; padding: 0.5rem !important; }
        }
    </style>
    @stack('scripts')
    @stack('scripts')

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

