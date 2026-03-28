<div class="modal fade" id="reasonModal" tabindex="-1" role="dialog" aria-labelledby="reasonEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{route('admin.business-settings.wholesaler-registration-reason.add')}}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title flex-grow-1 text-center text-capitalize" id="reasonEditModalLabel">{{translate('why_join_us')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @php($activeLanguage = in_array(getDefaultLanguage(), $languages ?? [], true) ? getDefaultLanguage() : $defaultLanguage)
                <ul class="nav nav-tabs mb-4">
                    @foreach($languages as $lang)
                    <li class="nav-item">
                        <a class="nav-link form-system-language-tab {{ $lang == $activeLanguage ? 'active' : '' }}"
                            href="javascript:" id="{{ $lang }}-link">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>
                <div class="modal-body">
                    @foreach($languages as $lang)
                    <input type="hidden" name="lang[]" value="{{ $lang }}">

                    <div class="form-group form-system-language-form {{ $lang }}-form {{ $lang != $activeLanguage ? 'd-none' : '' }}"
                        id="{{ $lang }}-form">
                        <div class="form-group">
                            <label class="title-color">{{translate('title')}} ({{ strtoupper($lang) }})</label>
                            <input type="text" name="title[]" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="title-color text-capitalize">{{translate('short_description')}} ({{ strtoupper($lang) }})</label>
                            <textarea class="form-control" name="description[]" rows="4" placeholder="{{translate('write_description').'...'}}"></textarea>
                        </div>
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
                                    <input type="checkbox" class="switcher_input" value="1" name="status">
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('close')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('save')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
