<?php

namespace App\Imports;

use App\Models\Warranty;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;

class SerialImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithChunkReading
{
    public $created = 0;
    public $updated = 0;
    public $failed = 0;
    public $errors = [];
    public $errorFilePath = null;

    /**
     * Handle each row of import
     */
    public function model(array $row): ?Warranty
    {
        try {
            if (empty($row['serial_number'])) {
                throw new \Exception('Serial number is missing.');
            }

            $existing = Warranty::where('serial_number', $row['serial_number'])->first();

            if ($existing) {
                throw new \Exception("Duplicate serial number: {$row['serial_number']} already exists in the system.");
            }

            $warranty = Warranty::create([
                'serial_number' => $row['serial_number'],
                'product_id' => $row['product_id'] ?? null,
                'warranty_months' => $row['warranty_months'] ?? null,
                'status' => 'preactivated',
            ]);

            $this->created++;
            return $warranty;

        } catch (\Exception $e) {
            $this->failed++;
            $this->errors[] = [
                'row' => $row,
                'error' => $e->getMessage(),
            ];
            return null;
        }
    }

    /**
     * Validation rules for each CSV row
     */
    public function rules(): array
    {
        return [
            'serial_number' => 'required|string',
            'product_id' => 'nullable|integer',
            'warranty_months' => 'required|integer|min:1',
        ];
    }
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->failed++;
            $this->errors[] = [
                'row' => $failure->values(),
                'error' => $failure->errors()[0] ?? 'Validation failed',
            ];
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}