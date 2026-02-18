@php
$content = $jsonData['content'] ?? [];

@endphp

@php
$languages = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = $languages[0] ?? 'en';
@endphp

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
<div class="card p-4 mb-4">
    <form action="{{ route('admin.content-management.download-app.heading.update') }}" method="POST">
    @csrf
    @method('PUT')

    @foreach($languages as $lang)
    @php
        $headingTranslations = $translations[$lang]['heading'] ?? [];
        $translatedHeading = $headingTranslations ?? '';
    @endphp

    <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
        id="{{ $lang }}-form">
        <div class="row">
            <input type="hidden" name="lang[]" value="{{ $lang }}">
            <div class="col-lg-6">
                <label class="title-color">{{ translate('heading') }} ({{ strtoupper($lang) }})</label>
                <input type="text" name="heading[]" class="form-control"
                    value="{{ $lang == $defaultLanguage ? $content['heading'] : (is_array($translatedHeading) ? ($translatedHeading[$lang] ?? '') : $translatedHeading) }}"
                    {{ $lang == $defaultLanguage ? 'required' : '' }}
                    placeholder="{{ translate('enter_heading') }}">
            </div>

        </div>
    </div>
    @endforeach

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="reset" class="btn btn-secondary">{{ translate('reset') }}</button>
        <button type="submit" class="btn btn--primary">{{ translate('update') }}</button>
    </div>
</form>
</div>

<div class="card">
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Platform</th>
            <th>Alt</th>
            <th>Image</th>
            <th class="text-center">Action</th>
        </tr>
    </thead>
    <tbody>
        {{-- Android Button --}}
        @if (!empty($content['android_button']))
        <tr>
            <td>Android</td>
            <td>{{ $content['android_button']['alt'] ?? '' }}</td>
            <td>
                <img src="{{ asset('uploads/' . $content['android_button']['image']) }}"
                    alt="{{ $content['android_button']['alt'] ?? '' }}" height="40">
            </td>
            <td class="d-flex justify-content-center">
                <a href="#" class="btn btn-outline-primary btn-sm square-btn" data-bs-toggle="modal"
                    data-bs-target="#editDownloadModal" data-key="android_button"
                    data-alt="{{ $content['android_button']['alt'] ?? '' }}"
                    data-image="{{ $content['android_button']['image'] ?? '' }}">
                    <i class="tio-edit"></i>
                </a>

               
            </td>
        </tr>
        @endif

        {{-- iOS Button --}}
        @if (!empty($content['ios_button']))
        <tr>
            <td>iOS</td>
            <td>{{ $content['ios_button']['alt'] ?? '' }}</td>
            <td>
                <img src="{{ asset('uploads/' . $content['ios_button']['image']) }}"
                    alt="{{ $content['ios_button']['alt'] ?? '' }}" height="40">
            </td>
            <td class="d-flex justify-content-center">

                <a href="#" class="btn btn-outline-primary btn-sm square-btn" data-bs-toggle="modal"
                    data-bs-target="#editDownloadModal" data-key="ios_button"
                    data-alt="{{ $content['ios_button']['alt'] ?? '' }}"
                    data-image="{{ $content['ios_button']['image'] ?? '' }}">
                    <i class="tio-edit"></i>
                </a>
                
            </td>
        </tr>
        @endif

        {{-- Mockup Image --}}
        @if (!empty($content['mockup_image']))
        <tr>
            <td>Mockup</td>
            <td>{{ $content['mockup_image']['alt'] ?? '' }}</td>
            <td>
                <img src="{{ asset('uploads/' . $content['mockup_image']['image']) }}"
                    alt="{{ $content['mockup_image']['alt'] ?? '' }}" height="40">
            </td>
            <td class="d-flex justify-content-center">
                <a href="#" class="btn btn-outline-primary btn-sm square-btn" data-bs-toggle="modal"
                    data-bs-target="#editDownloadModal" data-key="mockup_image"
                    data-alt="{{ $content['mockup_image']['alt'] ?? '' }}"
                    data-image="{{ $content['mockup_image']['image'] ?? '' }}">
                    <i class="tio-edit"></i>
                </a>
                
            </td>
        </tr>
        @endif

        {{-- App Logo --}}
        @if (!empty($content['app_logo']))
        <tr>
            <td>App Logo</td>
            <td>{{ $content['app_logo']['alt'] ?? '' }}</td>
            <td>
                <img src="{{ asset('uploads/' . $content['app_logo']['image']) }}"
                    alt="{{ $content['app_logo']['alt'] ?? '' }}" height="40">
            </td>
            <td class="d-flex justify-content-center">
                <a href="#" class="btn btn-outline-primary btn-sm square-btn" data-bs-toggle="modal"
                    data-bs-target="#editDownloadModal" data-key="app_logo"
                    data-alt="{{ $content['app_logo']['alt'] ?? '' }}"
                    data-image="{{ $content['app_logo']['image'] ?? '' }}">
                    <i class="tio-edit"></i>
                </a>
             
            </td>
        </tr>
        @endif
    </tbody>
</table>
</div>

<div class="modal fade" id="editDownloadModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.content-management.download-app.update') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="download_app">
            <input type="hidden" name="key" id="edit-key">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">{{translate('Edit Download App Item')}}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-alt" class="form-label">{{translate('Alt Text')}}</label>
                        <input type="text" class="form-control" name="alt" id="edit-alt" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-image" class="form-label">{{translate('Image ')}}(optional)</label>
                        <input type="file" class="form-control" name="image" id="edit-image" accept="image/*">
                        <div class="mt-2">
                            <img id="current-image" src="" alt="" height="50">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Cancel')}}</button>
                </div>
            </div>
        </form>
    </div>
</div>




<script>
    document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('[data-bs-target="#editDownloadModal"]');
    editButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit-key').value = btn.getAttribute('data-key');
            document.getElementById('edit-alt').value = btn.getAttribute('data-alt');
            const imagePath = btn.getAttribute('data-image');
            const currentImage = document.getElementById('current-image');

            if (imagePath) {
                currentImage.src = `/uploads/${imagePath}`;
                currentImage.style.display = 'block';
            } else {
                currentImage.src = '';
                currentImage.style.display = 'none';
            }
        });
    });
});

</script>