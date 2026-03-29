<table>
    <thead>
        <tr>
            <th>{{ translate('SL') }}</th>
            <th>{{ translate('Subject') }}</th>
            <th>{{ translate('Pipeline') }}</th>
            <th>{{ translate('Source') }}</th>
            <th>{{ translate('Name') }}</th>
            <th>{{ translate('Email') }}</th>
            <th>{{ translate('Phone') }}</th>
            <th>{{ translate('Owner') }}</th>
            <th>{{ translate('Department') }}</th>
            <th>{{ translate('Employee') }}</th>
            <th>{{ translate('Status') }}</th>
            <th>{{ translate('Received At') }}</th>

        </tr>
    </thead>
    <tbody>
        @foreach($messages as $key => $msg)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>{{ $msg->subject ?? 'No Subject' }}</td>
            <td>{{ ucfirst($msg->pipeline) }} - {{ $msg->message_type }}</td>
            <td>{{ $msg->source_id ?? 'N/A' }}</td>
            <td>{{ $msg->sender_name ?? 'Unassigned' }}</td>
            <td>{{ $msg->sender_email }}</td>
            <td>{{ $msg->sender_phone }}</td>
            <td>{{ $msg->owner?->name ?? 'Not Assigned' }}</td>
            <td>{{ $msg->department?->getTranslatedField('name') ?? 'No Department' }}</td>
            <td>{{ $msg->employee?->name ?? 'Not Assigned' }}</td>
            <td>{{ ucfirst($msg->status) }}</td>
            <td>{{ $msg->created_at->format('d M, Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>