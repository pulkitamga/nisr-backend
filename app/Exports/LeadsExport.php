<?php

namespace App\Exports;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class LeadsExport implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        private readonly Collection $leads,
        private readonly ?Request $request = null,
    ) {
    }

    public function collection(): Collection
    {
        return collect($this->rows());
    }

    public function rows(): array
    {
        return $this->leads->values()->map(function (Lead $lead, int $index) {
            $inbox = $lead->inboxMessages->first();

            return [
                $index + 1,
                (string) ($inbox?->subject ?? translate('No Subject')),
                $this->partyTypeLabel($lead->party_type),
                (string) ($inbox?->sender_name ?? $lead->full_name ?: translate('Unknown')),
                (string) ($inbox?->sender_email ?? translate('N/A')),
                $this->preserveAsText((string) ($inbox?->sender_phone ?? translate('N/A'))),
                (string) ($lead->owner?->name ?? translate('Unassigned')),
                $this->departmentName($lead),
                (string) ($lead->employee?->name ?? translate('Unassigned')),
                $this->localizedLabel($lead->priority),
                \App\Utils\crm_status_label($lead->status),
                $lead->created_at?->format('Y-m-d H:i') ?? '-',
            ];
        })->all();
    }

    public function headings(): array
    {
        return [
            translate('SL'),
            translate('Subject'),
            translate('Party_Type'),
            translate('Party_Name'),
            translate('Email'),
            translate('Phone'),
            translate('Owner'),
            translate('Department'),
            translate('Employee'),
            translate('Priority'),
            translate('Status'),
            translate('Created_At'),
        ];
    }

    public function title(): string
    {
        return translate('Lead List');
    }

    public function titleLabel(): string
    {
        return $this->title();
    }

    public function filterSummary(): string
    {
        $request = $this->request ?? new Request();
        $status = $request->has('status')
            ? (string) $request->get('status')
            : 'new';

        return implode(' | ', [
            translate('Search') . ': ' . (trim((string) $request->get('searchValue', '')) ?: translate('All')),
            translate('Status') . ': ' . ($status === 'all' ? translate('All') : \App\Utils\crm_status_label($status)),
            translate('DATE') . ': ' . (trim((string) $request->get('filter_date', '')) ?: translate('All')),
        ]);
    }

    private function partyTypeLabel(?string $partyType): string
    {
        if (!$partyType) {
            return translate('Unknown');
        }

        return match ($partyType) {
            'company' => translate('Company'),
            'contact' => translate('Contact'),
            default => Str::headline($partyType),
        };
    }

    private function localizedLabel(?string $value): string
    {
        if (!$value) {
            return translate('N/A');
        }

        $translated = translate($value);

        return $translated !== $value
            ? $translated
            : Str::headline($value);
    }

    private function preserveAsText(string $value): string
    {
        $trimmedValue = trim($value);

        if ($trimmedValue === '' || in_array($trimmedValue, ['N/A', '-'], true)) {
            return $value;
        }

        if (!preg_match('/^[0-9+()\-\s]+$/', $trimmedValue)) {
            return $value;
        }

        return '="' . str_replace('"', '""', $trimmedValue) . '"';
    }

    private function departmentName(Lead $lead): string
    {
        $department = $lead->department;

        if (!$department) {
            return translate('N/A');
        }

        if ($department->relationLoaded('translations')) {
            return (string) ($department->getTranslatedField('name') ?? $department->name ?? translate('N/A'));
        }

        return (string) ($department->name ?? translate('N/A'));
    }
}
