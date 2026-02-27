<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>نظام فوترة المياه المسبقة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .navbar-brand { font-weight: 700; }
        .card { border: 0; box-shadow: 0 4px 20px rgba(25, 32, 56, 0.08); }
        .stat-number { font-size: 1.6rem; font-weight: 700; }
        .page-header,
        .filter-form,
        .actions-row {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            align-items: center;
        }
        .page-header { justify-content: space-between; }
        .filter-form .form-control { min-width: 90px; }
        .table-responsive .table th,
        .table-responsive .table td { white-space: nowrap; }
        .topup-form { display: flex; gap: .25rem; flex-wrap: wrap; }
        .topup-form .form-control-sm { min-width: 92px; }
        @media (max-width: 767.98px) {
            .container { padding-left: .75rem; padding-right: .75rem; }
            .page-header h4 { width: 100%; margin-bottom: .25rem; }
            .filter-form { width: 100%; }
            .filter-form .form-control,
            .filter-form .btn { width: 100%; }
            .actions-row .btn,
            .actions-row form { width: 100%; }
            .actions-row form .btn { width: 100%; }
            .table-responsive .table { font-size: .84rem; }
            .navbar .btn { width: 100%; margin-top: .5rem; }
            .topup-form .form-control-sm,
            .topup-form .btn { width: 100%; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">💧 نظام الفوترة</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">لوحة التحكم</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('customers.index') }}">المشتركون</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('billing.index') }}">إدخال القراءات</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('billing.invoices') }}">الفواتير</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('reports.monthly') }}">التقارير</a></li>
            </ul>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-light btn-sm">تسجيل الخروج</button>
            </form>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
