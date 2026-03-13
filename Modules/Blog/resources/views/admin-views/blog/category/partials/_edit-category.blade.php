<div class="category-edit-form d--none">
    <div class="card shadow-sm">
        <div class="card-header shadow-none">
            <h5 class="m-0">{{ translate('Update_Category') }}</h5>
        </div>

        <div class="card-body">
            <div class="position-relative nav--tab-wrapper">
                <ul class="nav nav-tabs mb-4 category-edit-lang-nav">
                    @foreach($languages as $lang)
                        <li class="nav-item">
                            <a class="nav-link category-edit-lang-btn {{ $lang == $defaultLanguage ? 'active' : '' }}"
                               href="javascript:"
                               data-lang="{{ $lang }}"
                               data-target=".category-edit-lang-content-{{ $lang }}"
                               id="edit-{{ $lang }}-link">
                                {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                            </a>
                        </li>
                    @endforeach

                    <div class="nav--tab__prev">
                        <button class="btn btn-circle border-0 bg-white text-primary">
                            <i class="fi fi-sr-angle-left"></i>
                        </button>
                    </div>
                    <div class="nav--tab__next">
                        <button class="btn btn-circle border-0 bg-white text-primary">
                            <i class="fi fi-sr-angle-right"></i>
                        </button>
                    </div>
                </ul>
            </div>

            <form action="{{ route('admin.blog.category.update') }}" method="POST" class="category-form-submit" id="blog-category-update-form">
                @csrf
                <div class="mb-4">
                    <div class="category-edit-section">
                        @foreach($languages as $lang)
                            <div class="category-edit-lang-content category-edit-lang-content-{{ $lang }} {{ $lang != $defaultLanguage ? 'd-none' : '' }}">
                                <div class="form-group">
                                    <label class="form-label category-label">
                                        {{ translate('Category_Name') }} ({{ strtoupper($lang) }})
                                    </label>
                                    <input type="text"
                                           name="name[{{ $lang }}]"
                                           class="form-control category_name"
                                           id="edit-{{ $lang }}_category_name"
                                           placeholder="{{ translate('ex') . ':' . translate('LUX') }}"
                                           {{ $lang == $defaultLanguage ? 'required' : '' }}>
                                </div>
                            </div>
                            <input type="hidden" name="lang[{{ $lang }}]" value="{{ $lang }}" id="edit-lang-{{ $lang }}">
                        @endforeach
                    </div>
                </div>

                <input type="hidden" name="id" value="" id="edit-category-id">
                <div class="d-flex flex-wrap gap-3 justify-content-end">
                    <button type="reset" id="category-form-cancel-btn" class="btn btn-secondary">
                        {{ translate('Cancel') }}
                    </button>
                    <button class="btn btn-primary category-form-submit-btn"
                            data-type="update"
                            data-form="#blog-category-update-form"
                            data-route="{{ route('admin.blog.category.update') }}">
                        {{ translate('Update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editTabButtons = document.querySelectorAll('.category-edit-lang-btn');

    editTabButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetSelector = btn.dataset.target;

            // Deactivate all edit tabs
            editTabButtons.forEach(b => b.classList.remove('active'));

            // Activate clicked tab
            btn.classList.add('active');

            // Hide all edit contents
            document.querySelectorAll('.category-edit-lang-content').forEach(content => {
                content.classList.add('d-none');
            });

            // Show selected edit content
            const targetContent = document.querySelector(targetSelector);
            if (targetContent) {
                targetContent.classList.remove('d-none');
            }
        });
    });
});
</script>
