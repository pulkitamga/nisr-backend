<div class="col-sm-6 col-lg-4">
    <a class="business-analytics card py-3" href="#">
        <h5 class="business-analytics__subtitle">{{ $title }}</h5>
        <div class="d-flex flex-wrap gap-2">
            @foreach ($counts as $label => $count)
                <div class="text-center">
                    <small>{{ translate($label) }}</small>
                    <h6>{{ $count }}</h6>
                </div>
            @endforeach
        </div>
        <img src="{{ asset('assets/back-end/img/complaints/' . $img) }}" width="30" height="30" class="business-analytics__img" alt="{{ $title }}">
    </a>
</div>