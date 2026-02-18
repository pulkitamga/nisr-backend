@extends('layouts.back-end.app')

@section('title', translate('edit_blog'))

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-title">{{ translate('edit_blog') }}</h1>
    </div>

    <form action="{{ route('admin.content-management.blog.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label">{{ translate('heading') }}</label>
                <input type="text" name="heading" class="form-control" value="{{ old('heading', $blog->heading) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ translate('category') }}</label>
                <select name="category" class="form-control" required>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ $blog->category == $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">{{ translate('description') }}</label>
            <textarea name="description" class="form-control" rows="5" required>{{ old('description', $blog->description) }}</textarea>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label">{{ translate('blog_type') }}</label>
                <select name="blog_type" class="form-control" required>
                    @foreach($blogTypes as $type)
                        <option value="{{ $type }}" {{ $blog->blog_type == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ translate('image') }}</label>
                <input type="file" name="image" class="form-control">
                @if ($blog->image)
                    <img src="{{ Storage::url($blog->image) }}" class="mt-2" width="100" alt="Current Image">
                @endif
            </div>
        </div>

        <button type="submit" class="btn btn--primary">{{ translate('update') }}</button>
        <a href="{{ route('admin.content-management.blog') }}" class="btn btn-secondary">{{ translate('cancel') }}</a>
    </form>
</div>
@endsection
