@extends('layouts.back-end.app')

@section('title', translate('Notifications'))

@section('content')
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            {{ translate('Notifications') }}
        </h2>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ translate('SL') }}</th>
                                    <th>{{ translate('Title') }}</th>
                                    <th>{{ translate('Message') }}</th>
                                    <th>{{ translate('Type') }}</th>
                                    <th>{{ translate('received_at') }}</th>
                                    <th class="text-center">{{ translate('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($notifications as $i => $n)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($n->title, 40) }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($n->message, 100) }}</td>
                                    <td>
                                        @if($n->notifiable_type)
                                            {{ class_basename($n->notifiable_type) }}
                                        @else
                                            {{ translate('General') }}
                                        @endif
                                    </td>
                                    <td>{{ $n->created_at->diffForHumans() }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.notifications.view', $n->id) }}"
                                           class="btn btn-outline-info btn-sm" title="{{ translate('View') }}">
                                            <i class="tio-open-in-new"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">{{ translate('no_notifications_found') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                      <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $notifications->appends(request()->query())->links() !!}
            </div>
        </div>

        @if($notifications->isEmpty())
        @include('layouts.back-end._empty-state', ['text'=>'no_record_found','image'=>'default'])
        @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection