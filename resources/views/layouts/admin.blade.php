<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - {{ config('app.name') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- App CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
        }
        nav.sidebar {
            width: 220px;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        nav.sidebar a {
            text-decoration: none;
            color: #333;
        }
        nav.sidebar a:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar d-flex flex-column p-3 border-end">
        <h5 class="mb-4">{{ config('app.name') }} Admin</h5>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-1">
                <a href="{{ route('admin.dashboard') }}" class="nav-link link-dark">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('admin.posts.index') }}" class="nav-link link-dark">
                    <i class="bi bi-card-text"></i> Manage Posts
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('admin.posts.create') }}" class="nav-link link-dark">
                    <i class="bi bi-plus-circle"></i> Create Post
                </a>
            </li>
            <!-- Add other admin links here -->
        </ul>

        <hr>
        <div class="mt-auto">
            <p class="small mb-1">Logged in as:</p>
            <strong>{{ auth()->user()->name }}</strong>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button class="btn btn-sm btn-outline-danger w-100" type="submit">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1 p-4">
        @yield('content')
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- App JS -->
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
