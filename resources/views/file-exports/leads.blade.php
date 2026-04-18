<table>
    <thead>
        <tr>
            <th>{{ translate('SL') }}</th>
            <th>{{ translate('Subject') }}</th>
            <th>{{ translate('Party_Type') }}</th>
            <th>{{ translate('Party_Name') }}</th>
            <th>{{ translate('Contact Email') }}</th>
            <th>{{ translate('Contact Phone') }}</th>
            <th>{{ translate('Owner') }}</th>
            <th>{{ translate('Department') }}</th>
            <th>{{ translate('Employee') }}</th>
            <th>{{ translate('Priority') }}</th>
            <th>{{ translate('Status') }}</th>
            <th>{{ translate('Created_At') }}</th>

        </tr>
    </thead>
    <tbody>
        @foreach($leads as $key => $lead)
        @php
        $inbox = $lead->inboxMessages->first();
        @endphp
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $inbox?->subject ?? 'N/A' }}</td>
            <td>{{ ucfirst($lead->party_type ?? 'Unknown') }}</td>
            <td>{{ $inbox?->sender_name ?? 'Unknown' }}</td>
            <td>{{ $inbox?->sender_email ?? 'N/A' }}</td>
            <td>{{ $inbox?->sender_phone ?? 'N/A' }}</td>
            <td>{{ $lead->owner?->name ?? 'Not Assigned' }}</td>
            <td>{{ $lead->department?->getTranslatedField('name') ?? 'No Department' }}</td>
            <td>{{ $lead->employee?->name ?? 'Not Assigned' }}</td>
            <td>{{ $lead->priority ?? 'N/A' }}</td>
            <td>{{ ucfirst($lead->status) }}</td>
            <td>{{ $lead->created_at->format('d M, Y H:i A') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>