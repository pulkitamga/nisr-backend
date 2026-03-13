<!-- resources/views/front-end/warranty/claim-form.blade.php -->
@extends('layouts.front-end.app')

@section('title', translate('Claim Warranty'))

@section('content')
<div class="container my-5 text-align-direction">
    <div class="card">
        <div class="card-body">
            <h3>{{ translate('Submit Warranty Claim') }}</h3>
            <form action="{{ route('warranty.claim.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="warranty_public_id" value="{{ $warranty->warranty_public_id }}">

                <div class="form-group">
                    <label for="subject">{{ translate('Subject') }}</label>
                    <input type="text" name="subject" id="subject" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="details">{{ translate('Details about Claim') }}</label>
                    <textarea name="details" id="details" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <label for="issue">{{ translate('Issue Description') }}</label>
                    <textarea name="issue" id="issue" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <label for="product_images">{{ translate('Product Images') }}</label>
                    <input type="file" name="product_images[]" id="product_images" multiple class="form-control" accept="image/*" required>
                </div>

                <button type="submit" class="btn btn--primary">{{ translate('Submit Claim') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
