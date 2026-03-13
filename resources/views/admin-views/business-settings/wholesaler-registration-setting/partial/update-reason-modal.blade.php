@php
$languages = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $language[0]['code'] ?? 'en';
@endphp
<div class="modal fade" id="update-vendor-registration-reason-modal" tabindex="-1" role="dialog" aria-labelledby="reasonEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{route('admin.business-settings.wholesaler-registration-reason.update',['id'=>$wholesalerRegistrationReason['id']])}}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title flex-grow-1 text-center text-capitalize" id="reasonEditModalLabel">{{translate('why_sell_with_us')}}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
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
                    @php
                    $title = optional(
                    $wholesalerRegistrationReason->translations
                    ->first(fn ($item) => $item['locale'] === $lang && $item['key'] === 'title')
                    )->value;

                    $description = optional(
                    $wholesalerRegistrationReason->translations
                    ->first(fn ($item) => $item['locale'] === $lang && $item['key'] === 'description')
                    )->value;
                    @endphp

                    <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
                        id="{{ $lang }}-form">
                        <div class="form-group">
                            <label class="title-color">{{ translate('title') }} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="title[]" class="form-control"
                                value="{{ $lang === $defaultLanguage ? $wholesalerRegistrationReason['title'] : $title }}"
                                placeholder="{{ translate('enter_title') }}" required>

                            <label class="title-color text-capitalize">{{ translate('short_description') }} ({{ strtoupper($lang) }})</label>
                            <textarea class="form-control" name="description[]" rows="4"
                                placeholder="{{ translate('write_description') . '...' }}">{{ $lang === $defaultLanguage ? $wholesalerRegistrationReason['description'] : $description }}</textarea>
                        </div>
                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                    </div>
                    @endforeach


                    <div class="form-group">
                        <label class="title-color">{{translate('priority')}}</label>
                        <select name="priority" class="form-control">
                            @for($index = 1; $index <= 15; $index++)
                                <option value="{{ $index }}">{{ $index }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="border rounded p-3 d-flex justify-content-between gap-2 align-items-center">
                            <div class="text-dark">{{translate('turning_status_off_will_not_show_this_reason_in_the_list').'.'}}</div>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="fw-semibold text-dark">{{translate('status')}}</span>
                                <label class="switcher mx-auto">
                                    <input type="checkbox" class="switcher_input" name="status" value="1" {{$wholesalerRegistrationReason['status'] == 1?'checked':'' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('close')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('save')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function bindLanguageTabEvents(modalId = 'update-vendor-registration-reason-modal', defaultLang = 'en') {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const tabContainer = modal.querySelector('.nav-tabs');

        if (!tabContainer) return;

        tabContainer.addEventListener('click', function(e) {
            const target = e.target;
            if (target.classList.contains('form-system-language-tab')) {
                e.preventDefault();

                // Remove active from all tabs
                tabContainer.querySelectorAll('.form-system-language-tab').forEach(tab => tab.classList.remove('active'));
                target.classList.add('active');

                // Hide all forms
                modal.querySelectorAll('.form-system-language-form').forEach(form => form.classList.add('d-none'));

                // Show selected lang form
                const lang = target.id.replace('-link', '');
                const activeForm = modal.querySelector(`#${lang}-form`);
                if (activeForm) activeForm.classList.remove('d-none');
            }
        });

        // Reset when modal shows
        modal.addEventListener('shown.bs.modal', function() {
            // Reset tabs
            tabContainer.querySelectorAll('.form-system-language-tab').forEach(tab => {
                tab.classList.toggle('active', tab.id === `${defaultLang}-link`);
            });

            // Reset forms
            modal.querySelectorAll('.form-system-language-form').forEach(form => {
                form.classList.toggle('d-none', form.id !== `${defaultLang}-form`);
            });
        });
    }
</script>
