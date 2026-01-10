<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="css/style.css">
    @yield('script')
</head>

<body id="home">
    <header>
        <nav>
            @if ($isLoggedIn)
                <span>Welcome, {{ $user['name'] }}</span>
                @if ($isAdmin)
                    <a href="/admin">Admin Panel</a>
                @endif
            @else
                <a href="/login">Login</a>
            @endif
        </nav>
        <div class="breadcrumbs">
            @foreach ($breadcrumbs as $crumb)
                <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                <span>/</span>
            @endforeach
        </div>
    </header>
    <main>
        @yield('body')
    </main>
    <footer>
        <p>Total Products: {{ $stats['totalProducts'] }}</p>
    </footer>
</body>

</html>
