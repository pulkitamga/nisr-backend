<?php

namespace App\Imports;

use App\Models\Warranty;
use Illuminate\Support\Facades\DB;
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
            $serialNumber = trim((string) ($row['serial_number'] ?? ''));

            if ($serialNumber === '') {
                throw new \Exception('Serial number is missing.');
            }

            $existing = Warranty::where('serial_number', $serialNumber)->first();

            if ($existing) {
                throw new \Exception("Duplicate serial number: {$serialNumber} already exists in the system.");
            }

            $productId = $this->resolveProductId($row['product_sku'] ?? null);

            $warranty = Warranty::create([
                'serial_number' => $serialNumber,
                'product_id' => $productId,
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
            'product_sku' => 'nullable|string|exists:products,code',
            'warranty_months' => 'required|integer|min:1',
        ];
    }

    private function resolveProductId(mixed $productSku): ?int
    {
        $sku = trim((string) $productSku);

        if ($sku === '') {
            return null;
        }

        $productId = DB::table('products')
            ->where('code', $sku)
            ->value('id');

        if (!$productId) {
            throw new \Exception("Product SKU not found: {$sku}.");
        }

        return (int) $productId;
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
