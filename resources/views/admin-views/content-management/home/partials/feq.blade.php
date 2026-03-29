<!-- Add Quotation Button -->
<div class="mb-3 text-end">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFaqModal">
        + Add Question
    </button>
</div>

<!-- FAQ Table -->
<table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle w-100 text-start">
    <thead class="thead-light thead-50 text-capitalize">
        <tr>
            <th>{{ __('Question') }}</th>
            <th>{{ __('Answer') }}</th>
            <th>{{ __('Action') }}</th>
        </tr>
    </thead>
    <tbody id="faq-table-body">
        @foreach($jsonData['faqs'] ?? [] as $index => $faq)
        <tr data-index="{{ $index }}">
            <td>{{ $translations[getDefaultLanguage()]['cards'][$index]['question'] ?? $faq['question'] }}</td>
            <td>{{ $translations[getDefaultLanguage()]['cards'][$index]['answer'] ?? $faq['answer'] }}</td>
            <td class="text-center d-flex gap-2">
                <!-- Edit Button -->
                <button type="button" class="btn btn-outline-primary btn-sm square-btn" data-index="{{ $index }}"
                    data-faq='@json($faq)' onclick="openEditModal(this)">
                    <i class="tio-edit"></i>
                </button>

                <!-- Delete Button -->
                <form method="POST" action="{{ route('admin.content-management.faqs.delete', ['index' => $index]) }}"
                    onsubmit="return confirm('{{ __('Are you sure you want to delete this FAQ?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm square-btn">
                        <i class="tio-delete"></i>
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>


<!-- Add FAQ Modal -->
<div class="modal fade" id="addFaqModal" tabindex="-1" aria-labelledby="addFaqModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.content-management.faqs.add') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add FAQ') }}</h5>
                    <button type="button" class="close cms-modal-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>{{ __('Question') }}</label>
                        <input type="text" name="question" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>{{ __('Answer') }}</label>
                        <textarea name="answer" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{ __('Add') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit FAQ Modal -->
<div class="modal fade" id="editFaqModal" tabindex="-1" aria-labelledby="editFaqModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editFaqForm" method="POST" action="{{ route('admin.content-management.faqs.update') }}">
            @csrf
            <input type="hidden" name="index" id="editFaqIndex">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Edit FAQ') }}</h5>
                    <button type="button" class="close cms-modal-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editQuestion" class="form-label">{{ __('Question') }}</label>
                        <input type="text" name="question" id="editQuestion" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAnswer" class="form-label">{{ __('Answer') }}</label>
                        <textarea name="answer" id="editAnswer" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{ __('Update FAQ') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('script')
<script>
    function openEditModal(button) {
        const index = button.getAttribute('data-index');
        const faq = JSON.parse(button.getAttribute('data-faq'));

        document.getElementById('editFaqIndex').value = index;
        document.getElementById('editQuestion').value = faq.question;
        document.getElementById('editAnswer').value = faq.answer;

        const modal = new bootstrap.Modal(document.getElementById('editFaqModal'));
        modal.show();
    }
</script>
@endpush

