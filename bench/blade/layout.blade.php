<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ trim($title) }}</title>
    <link rel="stylesheet" href="css/style.css">
    @yield('head')
    @yield('script')
</head>

<body id="catalog">
    <header>
        <nav>
            @if ($isLoggedIn)
                <span>Welcome, {{ $user['name'] }}</span>
                <span class="tier">Tier: {{ strtoupper(trim($user['tier'])) }}</span>
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

        <ul class="top-categories">
            @foreach ($topCategories as $category)
                <li>
                    <a href="{{ $category['url'] }}">{{ strtoupper(trim($category['label'])) }}</a>
                    @if (count($category['children']) > 0)
                        <ul>
                            @foreach ($category['children'] as $child)
                                <li><a href="{{ $child['url'] }}">{{ trim($child['label']) }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </header>

    <main>
        @yield('body')
    </main>

    <footer>
        <p>Total Products: {{ $stats['totalProducts'] }} · Orders: {{ $stats['totalOrders'] }} · Open: {{ $stats['openOrders'] }}</p>
        <p>Store: {{ $store->name }} · Support: {{ $store->support->email }} · Timezone: {{ $store->support->timezone }}</p>
        @if ($stats['revenue'] > 90000)
            <p class="kpi">Revenue YTD: ${{ number_format($stats['revenue'], 2) }} · Conversion: {{ number_format($stats['conversionRate'], 1) }}%</p>
        @endif
    </footer>
</body>

</html>
