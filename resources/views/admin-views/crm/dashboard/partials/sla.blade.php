@foreach ($counts as $label => $count)
<div class="col-sm-6 col-lg-4">
    <a class="business-analytics card py-3" href="#">
        <h5 class="business-analytics__subtitle">{{ translate($label) }}</h5>
        <h2 class="business-analytics__title" id="count-{{ $label }}">{{ $count }}</h2>

        {{-- Dynamic Image Based on Label --}}
        @php
        $images = [
        'overdue slas' => 'overdue.png',
        'pending activities'=> 'clock.png',
        'voip calls today' => 'voip.png',
        ];

        $img = $images[strtolower($label)] ?? 'default.png'; // fallback
        @endphp

        <img src="{{ asset('assets/back-end/img/complaints/' . $img) }}"
            width="30" height="30"
            class="business-analytics__img"
            alt="{{ $label }}">
    </a>
</div>
@endforeach