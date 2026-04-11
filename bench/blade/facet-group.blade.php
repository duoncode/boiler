<section class="facet-group">
    <h4>{{ strtoupper(trim($facet['title'])) }}</h4>

    @if ($facet['expanded'])
        <ul>
            @foreach ($facet['options'] as $option)
                <li class="{{ $option['selected'] ? 'selected' : 'idle' }}">
                    <span>{{ trim($option['label']) }}</span>
                    <small>({{ $option['count'] }})</small>
                </li>
            @endforeach
        </ul>
    @else
        <p>Collapsed</p>
    @endif
</section>
