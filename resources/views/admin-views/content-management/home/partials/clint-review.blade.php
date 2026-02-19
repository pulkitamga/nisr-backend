@php
$languages = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = $languages[0] ?? 'en';
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"></h5>
    <button class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#addReviewModal">
        {{ translate('Add Review') }}
    </button>
</div>
<table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle w-100 text-start">
    <thead class="thead-light thead-50 text-capitalize">
        <tr>
            <th>{{translate('Rating')}}</th>
            <th>{{translate('Name')}}</th>
            <th>{{translate('Review')}}</th>
            <th>{{translate('Image')}}</th>
            <th>{{translate('Action')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($jsonData['clients'] ?? [] as $index => $client)

        @php
        $titleLangMap = [];
        $descLangMap = [];

        foreach ($languages as $lang) {
        $titleLangMap[$lang] = $translations[$lang]['cards'][$index]['name'] ?? '';
        $descLangMap[$lang] = $translations[$lang]['cards'][$index]['review'] ?? '';

        }
        $titleLangMap[$defaultLanguage] = $client['name'] ?? '';
        $descLangMap[$defaultLanguage] = $client['review'] ?? '';

        @endphp
        <tr data-index="{{ $index }}">
            <td>{{ $client['rating'] }}</td>
            <td>{{$client['name']}} </td>
            <td>{{ $client['review'] }}</td>
            <td><img src="{{ $client['image'] }}" width="50" alt="{{ $client['name'] }}"></td>
            <td class="text-center d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm square-btn btn-edit" data-index="{{ $index }}"
                    data-rating="{{ $client['rating'] }}" data-name='@json($titleLangMap)'
                    data-review='@json($descLangMap)' data-image="{{ $client['image'] }}"> <i
                        class="tio-edit"></i></button>
                <form method="POST" action="{{ route('admin.content-management.client_review.delete') }}"
                    class="d-inline delete-review-form">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="index" value="{{ $index }}">
                    <button type="submit" class="btn btn-outline-danger btn-sm square-btn delete-review-btn">
                        <i class="tio-delete"></i>
                    </button>
                </form>

            </td>
        </tr>
        @endforeach
    </tbody>
</table>



<!-- Add Review Modal -->
<div class="modal fade" id="addReviewModal" tabindex="-1" aria-labelledby="addReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.content-management.client-review.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addReviewModalLabel">{{ translate('Add New Review') }}</h5>

                    <button type="button" class="close cms-modal-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <ul class="nav nav-tabs mb-4">
                    @foreach($languages as $lang)
                    <li class="nav-item">
                        <a class="nav-link form-system-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                            href="javascript:" id="{{ $lang }}-link">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>
                <div class="modal-body">
                    @foreach($languages as $lang)
                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                    <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
                        id="{{ $lang }}-form">
                        <div class="form-group">

                            <label class="form-label">{{ translate('Name') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="name[]" class="form-control lang-name"
                                data-lang="{{ $lang }}"
                                {{ $lang == $defaultLanguage ? 'required' : '' }}>

                            <label class="form-label">{{ translate('Review') }} ({{ strtoupper($lang) }})</label>
                            <textarea name="review[]" class="form-control lang-review"
                                data-lang="{{ $lang }}"
                                rows="3"
                                {{ $lang == $defaultLanguage ? 'required' : '' }}></textarea>
                        </div>
                    </div>

                    @endforeach
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Rating (out of 5)') }}</label>
                        <input type="varchar" name="rating" class="form-control" min="1" max="5" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Image') }}</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary">{{ translate('Submit') }}</button>
                </div>
            </div>
        </form>
    </div>

</div>


<div class="modal fade" id="editReviewModal" tabindex="-1" aria-labelledby="editReviewLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editReviewForm" method="POST" action="{{ route('admin.content-management.client_review.update') }}"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="index" id="edit-index">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close cms-modal-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <ul class="nav nav-tabs mb-4">
                    @foreach($languages as $lang)
                    <li class="nav-item">
                        <a class="nav-link form-system-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                            href="javascript:" id="{{ $lang }}-link">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>
                <div class="modal-body">
                    @foreach($languages as $lang)

                    <input type="hidden" name="lang[]" value="{{ $lang }}">

                    <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
                        id="{{ $lang }}-form">

                        <div class="form-group">
                            <label>{{ translate('name') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="name[]" id="edit-name" class="form-control lang-name"
                                data-lang="{{ $lang }}"
                                {{ $lang == $defaultLanguage ? 'required' : '' }}>


                            <label>{{ translate('review') }} ({{ strtoupper($lang) }})</label>
                            <textarea name="review[]" class="form-control lang-review"
                                data-lang="{{ $lang }}"
                                rows="3"
                                {{ $lang == $defaultLanguage ? 'required' : '' }}></textarea>
                        </div>
                    </div>
                    @endforeach
                    <div class="mb-3">
                        <label for="edit-rating" class="form-label">Rating</label>
                        <input type="text" name="rating" id="edit-rating" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label><br>
                        <img id="edit-image-preview" src="" alt="Image Preview" width="100"
                            style="cursor:pointer; border:1px solid #ccc; padding:4px;">
                        <input type="file" name="image_file" id="edit-image-file" class="form-control mt-2"
                            style="display: none;" accept="image/*">
                        <input type="hidden" name="image_url" id="edit-image-url">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>


<script>
    $(document).on('click', '.delete-review-btn', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');

        Swal.fire({
            title: 'Confirm Deletion',
            text: 'Are you sure you want to delete this review?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); 
            }
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
        var editButtons = document.querySelectorAll('.btn-edit');
        var editModal = new bootstrap.Modal(document.getElementById('editReviewModal'));

        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                document.getElementById('edit-index').value = this.dataset.index;
                document.getElementById('edit-rating').value = this.dataset.rating;
                document.getElementById('edit-image-preview').src = this.dataset.image;
                document.getElementById('edit-image-url').value = this.dataset.image;
                const name = JSON.parse(this.dataset.name || '{}');
                const review = JSON.parse(this.dataset.review || '{}');

                document.querySelectorAll('.lang-name').forEach(function(input) {
                    const lang = input.dataset.lang;
                    input.value = name[lang] || '';
                });

                document.querySelectorAll('.lang-review').forEach(function(textarea) {
                    const lang = textarea.dataset.lang;
                    textarea.value = review[lang] || '';
                });
                editModal.show();
            });
        });

        document.getElementById('edit-image-preview').addEventListener('click', function() {
            document.getElementById('edit-image-file').click();
        });

        // Optional: live preview of selected image
        document.getElementById('edit-image-file').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit-image-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });

    $(document).on('click', '.form-system-language-tab', function() {
        const lang = $(this).attr('id').replace('-link', '');

        // Deactivate all tabs and content
        $(this).closest('.nav-tabs').find('.form-system-language-tab').removeClass('active');
        $(this).closest('.modal-content').find('.form-system-language-form').addClass('d-none');

        // Activate clicked tab and corresponding content
        $(this).addClass('active');
        $(this).closest('.modal-content').find(`#${lang}-form`).removeClass('d-none');
    });


    document.addEventListener("DOMContentLoaded", function() {
        const imagePreview = document.getElementById('edit-image-preview');
        const imageFileInput = document.getElementById('edit-image-file');
        const imageUrlInput = document.getElementById('edit-image-url');

        // Click on image preview to open file selector
        imagePreview.addEventListener('click', function() {
            imageFileInput.click();
        });

        // When a file is selected, show preview
        imageFileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imageUrlInput.value = ''; // clear image URL when uploading new one
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
