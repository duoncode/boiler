<aside class="sidebar">
    <h2>{{ trim($title) }}</h2>

    <div class="user-summary">
        <p>Logged in as: {{ $user['name'] }}</p>
        <p>Location: {{ $user['profile']['location'] }}</p>
        <p>Tier: {{ strtoupper(trim($user['tier'])) }}</p>
    </div>

    <div class="quick-stats">
        <p>Products: {{ $stats['totalProducts'] }}</p>
        <p>Orders: {{ $stats['totalOrders'] }}</p>
    </div>

    <div class="active-filters">
        <h3>Active filters</h3>
        @if (count($activeFilters) > 0)
            <ul>
                @foreach ($activeFilters as $filter)
                    <li>{{ $filter['label'] }}: {{ strtoupper(trim($filter['value'])) }}</li>
                @endforeach
            </ul>
        @else
            <p>No filters selected.</p>
        @endif
    </div>

    <div class="facets">
        @foreach ($facets as $facet)
            @include('facet-group', ['facet' => $facet])
        @endforeach
    </div>
</aside>
