<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Schedule</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{
            --bg0: #0b1220;
            --bg1: #0e1b3a;
            /* Glass (lighter for readability) */
            --glass: rgba(255,255,255,.86);
            --glass2: rgba(255,255,255,.78);
            --glassBorder: rgba(15,23,42,.10);
            --text: #0f172a;
            --muted: #4b5563;
            --gradA: linear-gradient(135deg, #7c3aed 0%, #2563eb 45%, #06b6d4 100%);
            --gradB: linear-gradient(135deg, rgba(124,58,237,.18) 0%, rgba(37,99,235,.16) 45%, rgba(6,182,212,.14) 100%);
        }

        body{
            background: radial-gradient(1200px 700px at 10% 0%, rgba(124,58,237,.22), transparent 60%),
                        radial-gradient(900px 600px at 90% 10%, rgba(6,182,212,.18), transparent 55%),
                        radial-gradient(900px 700px at 60% 100%, rgba(37,99,235,.18), transparent 55%),
                        linear-gradient(180deg, #0b1220 0%, #0b1220 35%, #eef2ff 100%);
            min-height: 100vh;
        }

        .app-nav{
            background: var(--gradA);
            box-shadow: 0 10px 30px rgba(2,6,23,.25);
        }

        .brand{
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .brand-avatar{
            width: 34px;
            height: 34px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(255,255,255,.55), rgba(255,255,255,.2));
            padding: 2px;
            box-shadow: 0 10px 24px rgba(2,6,23,.28);
            flex: 0 0 auto;
        }
        .brand-avatar-inner{
            width: 100%;
            height: 100%;
            border-radius: 999px;
            background: rgba(15,23,42,.22);
            border: 1px solid rgba(255,255,255,.35);
            overflow: hidden;
            display: grid;
            place-items: center;
        }
        .brand-avatar img{
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .brand-avatar-fallback{
            font-weight: 800;
            font-size: 14px;
            letter-spacing: .3px;
            color: rgba(255,255,255,.95);
        }

        .navbar-brand{
            font-weight: 700;
            letter-spacing: .2px;
        }

        .app-shell{
            padding-bottom: 48px;
        }

        .app-card{
            background: linear-gradient(135deg, var(--glass) 0%, var(--glass2) 100%);
            border: 1px solid var(--glassBorder);
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(2,6,23,.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            overflow: hidden;
            color: var(--text);
        }

        .app-card-header{
            background: linear-gradient(135deg, rgba(255,255,255,.72) 0%, rgba(255,255,255,.58) 100%);
            border-bottom: 1px solid rgba(15,23,42,.08);
        }

        .table thead th{
            position: sticky;
            top: 0;
            z-index: 1;
            background: rgba(255,255,255,.92) !important;
            backdrop-filter: blur(8px);
            color: var(--text);
        }

        table.table-sm td, table.table-sm th{
            vertical-align: middle;
        }

        .table{
            color: var(--text);
        }
        .table td, .table th{
            border-color: rgba(15,23,42,.10) !important;
        }
        .table-hover tbody tr:hover{
            background-color: rgba(15,23,42,.03);
        }

        .form-control{
            background: rgba(255,255,255,.96);
            border-color: rgba(15,23,42,.12);
            color: var(--text);
        }
        .form-control:focus{
            background: #fff;
            border-color: rgba(37,99,235,.35);
            box-shadow: 0 0 0 .25rem rgba(99,102,241,.18);
            color: var(--text);
        }
        .form-label, .text-muted, .small.text-muted{
            color: var(--muted) !important;
        }

        .alert{
            border-radius: 14px;
            border-color: rgba(15,23,42,.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: var(--text);
        }

        .btn-gradient{
            background: var(--gradA);
            border: 0;
            color: #fff;
            box-shadow: 0 12px 28px rgba(37,99,235,.25);
        }
        .btn-gradient:hover{
            filter: brightness(1.02);
            color: #fff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark app-nav mb-4">
        <div class="container">
            <a class="navbar-brand brand" href="{{ route('schedule.index') }}">
                @php
                    $avatarRelPath = 'images/kyla.jpg';
                    $avatarAbsPath = public_path($avatarRelPath);
                @endphp
                <span class="brand-avatar" aria-hidden="true">
                    <span class="brand-avatar-inner">
                        @if(file_exists($avatarAbsPath))
                            <img src="{{ asset($avatarRelPath) }}" alt="Kyla photo">
                        @else
                            <span class="brand-avatar-fallback">K</span>
                        @endif
                    </span>
                </span>
                <span>Kylas Sched</span>
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('schedule.index') }}">Daily Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reports.monthly') }}">Monthly Report</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="app-shell">
        @yield('content')
    </main>
</body>
</html>

