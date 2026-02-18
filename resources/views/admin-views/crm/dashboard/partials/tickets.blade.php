@foreach ($counts as $label => $count)
<div class="col-sm-6 col-lg-4">
    <a class="business-analytics card py-3" href="#">
        <h5 class="business-analytics__subtitle">{{ translate($label) }}</h5>
        <h2 class="business-analytics__title" id="count-{{ $label }}">{{ $count }}</h2>

        {{-- Dynamic Image Based on Label --}}
        @php
            $images = [
                'support' => 'new.png',
                'career' => 'career-tickets.png',
                'retail' => 'retail-tickets.png',
                'wholesale' => 'wholesaler-ticket.png',
                'complaint' => 'complaint -ticket.png',
                'service' => 'service-tickets.png',
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