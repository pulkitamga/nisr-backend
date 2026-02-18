@extends('layouts.back-end.app')

@section('title', translate('calendar'))

@section('content')

<div class="content container-fluid">

    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/support_ticket.png')}}" alt="">
            {{translate('calendar')}}
        </h2>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="eventFilter">{{ translate('Filter Events') }}</label>
                    <select class="form-control" id="eventFilter">
                        <option value="all">{{ translate('All') }}</option>
                        <option value="Task">{{ translate('Task') }}</option>
                        <option value="Call">{{ translate('Call') }}</option>
                        <option value="Note">{{ translate('Note') }}</option>
                        <option value="Activity">{{ translate('Activity') }}</option>
                    </select>
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <div class="calendar-legend ms-auto">
                        <span class="legend-item"><span class="legend-circle bg-blue"></span>{{ translate('Task') }}</span>
                        <span class="legend-item"><span class="legend-circle bg-green"></span>{{ translate('Call') }}</span>
                        <span class="legend-item"><span class="legend-circle bg-purple"></span>{{ translate('Note') }}</span>
                        <span class="legend-item"><span class="legend-circle bg-orange"></span>{{ translate('Activity') }}</span>
                        <span class="legend-item"><span class="legend-circle bg-red"></span>{{ translate('To_Do') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{ translate('calendar') }}</h5>
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTodoModal">
                            <i class="tio-add"></i> Add To-Do
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addTodoModal" tabindex="-1" aria-labelledby="addTodoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addTodoForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header  text-white">
                    <h5 class="modal-title" id="addTodoModalLabel">Add To-Do</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Date</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="note" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save To-Do</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/fullcalendar.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/popper.js')}}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        // Make calendar accessible globally
        window.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: window.innerWidth < 768 ? 'timeGridDay' : 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: {
                url: "{{ route('admin.crm.calendar.events') }}",
                failure: function() {
                    Swal.fire('Error!', 'Failed to load events. Please check the console.', 'error');
                }
            },
            editable: false,
            eventClick: function(info) {
                if (info.event.url) {
                    window.open(info.event.url, '_blank');
                    info.jsEvent.preventDefault();
                }
            },
            eventDidMount: function(info) {
                const tooltip = new bootstrap.Tooltip(info.el, {
                    title: 'Type: ' + info.event.extendedProps.type +
                        '\nEmployee: ' + (info.event.extendedProps.employee ?? '-') +
                        '\nDescription: ' + (info.event.extendedProps.description ?? '-'),
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            },
            windowResize: function() {
                calendar.changeView(window.innerWidth < 768 ? 'timeGridDay' : 'dayGridMonth');
            }
        });


        calendar.render();

        document.getElementById('eventFilter').addEventListener('change', function() {
            let filter = this.value.toLowerCase();
            calendar.getEvents().forEach(event => {
                let type = event.extendedProps.type.toLowerCase();
                if (filter === 'all' || type.includes(filter)) {
                    event.setProp('display', 'auto');
                } else {
                    event.setProp('display', 'none');
                }
            });
        });

        // Add To-do form submit
        document.getElementById('addTodoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch('{{ route('admin.crm.calendar.todo.add') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                .then(res => res.json())
                .then(data => {
                    console.log(data); // for debugging
                    if (data.status === 'success') {
                        Swal.fire('Added!', data.message, 'success');
                        var modal = bootstrap.Modal.getInstance(document.getElementById('addTodoModal'));
                        modal.hide();
                        window.calendar.refetchEvents(); // <-- use global calendar
                    } else {
                        Swal.fire('Error!', 'Could not add to-do.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                });
        });
    });
</script>
@endpush