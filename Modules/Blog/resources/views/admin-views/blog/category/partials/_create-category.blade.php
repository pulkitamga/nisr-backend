<div class="category-create-form">
    <div class="card shadow-sm">
        <div class="card-header shadow-none">
            <h4 class="m-0">{{ translate('Add_New_Category') }}</h4>
        </div>
        <div class="card-body">
            <div class="position-relative blog-lang-tab-wrapper">
                <ul class="nav nav-tabs mb-4 blog-lang-tab-nav">
                    @foreach($languages as $lang)
                    <li class="nav-item">
                        <a class="nav-link blog-lang-tab-btn {{ $lang == $defaultLanguage ? 'active' : '' }}"
                            href="javascript:"
                            data-lang="{{ $lang }}"
                            data-target=".blog-lang-content-{{ $lang }}">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <form action="{{ route('admin.blog.category.add') }}" method="POST" class="category-form-submit" id="blog-category-add-form">
                @csrf
                <div class="mb-4">
                    <div class="blog-lang-tab-content-wrapper">
                        @foreach($languages as $lang)
                        <div class="blog-lang-content blog-lang-content-{{ $lang }} {{ $lang != $defaultLanguage ? 'd-none' : '' }}">
                            <div class="form-group">
                                <label class="form-label">{{ translate('Category_Name') }} ({{ strtoupper($lang) }})</label>
                                <input type="text" name="name[{{ $lang }}]" class="form-control"
                                    placeholder="{{ translate('ex') . ':' . translate('LUX') }}">
                            </div>
                        </div>
                        <input type="hidden" name="lang[{{ $lang }}]" value="{{ $lang }}">
                        @endforeach
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3 justify-content-end">
                    <button type="reset" class="btn btn-secondary">{{ translate('Reset') }}</button>
                    <button class="btn btn--primary-2 category-form-submit-btn"
                        data-type="add"
                        data-form="#blog-category-add-form"
                        data-route="{{ route('admin.blog.category.add') }}">
                        {{ translate('Save') }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select all language tab buttons
    const langButtons = document.querySelectorAll('.blog-lang-tab-btn');

    langButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Remove active class from all buttons
            langButtons.forEach(btn => btn.classList.remove('active'));

            // Add active class to clicked button
            this.classList.add('active');

            // Hide all language content
            const allContents = document.querySelectorAll('.blog-lang-content');
            allContents.forEach(content => content.classList.add('d-none'));

            // Show target content
            const targetSelector = this.dataset.target;
            const targetContent = document.querySelector(targetSelector);
            if (targetContent) {
                targetContent.classList.remove('d-none');
            }
        });
    });
});
</script>
