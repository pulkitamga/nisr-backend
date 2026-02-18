@extends('layouts.back-end.app')

@section('title', translate('create_blog'))

@section('content')
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 text-capitalize">{{ translate('create_blog') }}</h2>
    </div>

    <form action="{{ route('admin.content-management.blog.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>{{ translate('heading') }}</label>
            <input type="text" name="heading" class="form-control" required>
        </div>

        <div class="form-group">
            <label>{{ translate('description') }}</label>
            <textarea name="description" rows="5" class="form-control" required></textarea>
        </div>

        <div class="form-group">
            <label>{{ translate('image') }}</label>
            <input type="file" name="image" class="form-control" required>
        </div>

        <div class="form-group">
            <label>{{ translate('blog_type') }}</label>
            <select name="blog_type" class="form-control" required>
                @foreach($blogTypes as $type)
                <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>{{ translate('category') }}</label>
            <select name="category" class="form-control" required>
                @foreach($categories as $category)
                <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mt-3">
            <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
        </div>
    </form>
</div>
@endsection
