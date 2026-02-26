@forelse($activities as $activity)
@if($loop->first)
<div class="table-responsive">
    <table
        style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
        <thead class="thead-light thead-50 text-capitalize">
            <tr>
                <th>{{ translate('Date') }}</th>
                <th>{{ translate('Type') }}</th>
                <th>{{ translate('Title') }}</th>
                <th>{{ translate('Employee') }}</th>
                <th>{{ translate('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @endif
            <tr>
                <td>{{ $activity->created_at->format('d M, Y ') }}</td>
                <td>{{ ucfirst($activity->activity_type) }}</td>
                <td>{{ $activity->title }}</td>
                <td>{{ $activity->employee->name ?? translate('Unassigned') }}</td>
                <td>
                    <button class="btn btn-sm btn-outline-success view-details" data-bs-toggle="modal" data-bs-target="#activityModal"
                        data-activity='@json($activity)'> <i class="tio-invisible"></i>
                    </button>
                </td>
            </tr>
            @if($loop->last)
        </tbody>
    </table>
</div>
<div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityModalLabel">{{ translate('Activity Details') }}</h5>
                <button type="button" class="btn btn-sm btn-outline--primary btn-close" data-bs-dismiss="modal" aria-label="Close"> &times;
                </button>
            </div>
            <div class="modal-body">
                <div id="activityDetails">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endif
@empty
@include('layouts.back-end._empty-state',['text'=>'no_Activity_Found'],['image'=>'default'])
@endforelse

<script>
document.addEventListener('DOMContentLoaded', function() {
    const textNA = @json(translate('N/A'));
    const textUnassigned = @json(translate('Unassigned'));
    const viewButtons = document.querySelectorAll('.view-details');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const activity = JSON.parse(this.getAttribute('data-activity'));
            const detailsContainer = document.getElementById('activityDetails');

            let detailsHtml = `
                <p><strong>{{ translate('Type') }}:</strong> ${activity.activity_type.charAt(0).toUpperCase() + activity.activity_type.slice(1)}</p>
                <p><strong>{{ translate('Title') }}:</strong> ${activity.title}</p>
                <p><strong>{{ translate('Subject') }}:</strong> ${activity.subject || textNA}</p>
                <p><strong>${activity.activity_type === 'task' ? '{{ translate('Due Date') }}' : '{{ translate('Date') }}'}:</strong> 
                    ${activity.note_date ? new Date(activity.note_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : new Date(activity.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</p>
                <p><strong>{{ translate('Employee') }}:</strong> ${activity.employee?.name || textUnassigned}</p>
            `;

           if (activity.details) {
    let detailsObj;

    // Parse JSON string details safely.
    if (typeof activity.details === 'string') {
        try {
            detailsObj = JSON.parse(activity.details);
        } catch (e) {
            detailsObj = { raw: activity.details };
        }
    } else {
        detailsObj = activity.details;
    }

    detailsHtml += `<hr><h6>{{ translate('Details') }}</h6>`;
   for (const [key, value] of Object.entries(detailsObj)) {
    let displayValue = value;

    // Format date-like values for readability.
    if (typeof value === 'string' && /\d{4}-\d{2}-\d{2}/.test(value)) {
        displayValue = new Date(value).toLocaleString('en-GB', { 
            day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' 
        });
    }

    detailsHtml += `<p><strong>${key.replace(/_/g, ' ').toUpperCase()}:</strong> ${displayValue}</p>`;
}

}


            detailsContainer.innerHTML = detailsHtml;
        });
    });
});

</script>

