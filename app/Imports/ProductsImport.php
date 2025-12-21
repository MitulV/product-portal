<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ProductsImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Generators' => new GeneratorsImport(),
        ];
    }
}

class GeneratorsImport implements ToModel, WithStartRow, WithValidation
{
    /**
     * Headers are at row 5 (A5 to Y5), data starts at row 6
     */
    public function startRow(): int
    {
        return 6;
    }

    /**
     * Map Excel columns A5-Y5 to product fields
     * Row 5 contains headers, data starts from row 6
     */
    public function model(array $row)
    {
        // Skip completely empty rows
        if (!array_filter($row, function($value) {
            return $value !== null && $value !== '';
        })) {
            return null;
        }

        // Map columns A5 to Y5 (indices 0-24) to product table fields
        return new Product([
            'hold_status'          => $this->cleanValue($row[0] ?? null),  // A5
            'hold_branch'          => $this->cleanValue($row[1] ?? null),  // B5
            'salesman'             => $this->cleanValue($row[2] ?? null),  // C5
            'opportunity_name'     => $this->cleanValue($row[3] ?? null),  // D5
            'hold_expiration_date' => $this->transformDate($row[4] ?? null), // E5
            'brand'                => $this->cleanValue($row[5] ?? null),  // F5
            'model_number'         => $this->cleanValue($row[6] ?? null),  // G5
            'est_completion_date'  => $this->transformDate($row[7] ?? null), // H5
            'total_cost'           => $this->transformNumeric($row[8] ?? null),  // I5
            'tariff_cost'          => $this->transformNumeric($row[9] ?? null),  // J5
            'sales_order_number'   => $this->cleanValue($row[10] ?? null), // K5
            'ipas_cpq_number'      => $this->cleanValue($row[11] ?? null), // L5
            'cps_po_number'        => $this->cleanValue($row[12] ?? null), // M5
            'ship_date'            => $this->transformDate($row[13] ?? null), // N5
            'voltage'              => $this->cleanValue($row[14] ?? null), // O5
            'phase'                => $this->cleanValue($row[15] ?? null), // P5
            'enclosure'            => $this->cleanValue($row[16] ?? null), // Q5
            'enclosure_type'       => $this->cleanValue($row[17] ?? null), // R5
            'tank'                 => $this->cleanValue($row[18] ?? null), // S5
            'controller_series'    => $this->cleanValue($row[19] ?? null), // T5
            'breakers'             => $this->cleanValue($row[20] ?? null), // U5
            'serial_number'        => $this->cleanValue($row[21] ?? null), // V5
            'unit_id'              => $this->cleanValue($row[22] ?? null), // W5
            'notes'                => $this->cleanValue($row[23] ?? null), // X5
            'tech_spec'            => $this->cleanValue($row[24] ?? null), // Y5
        ]);
    }

    /**
     * Clean string values - trim whitespace and convert empty strings to null
     */
    private function cleanValue($value)
    {
        if ($value === null) {
            return null;
        }
        
        $cleaned = trim((string) $value);
        return $cleaned === '' ? null : $cleaned;
    }

    /**
     * Transform numeric values for cost fields
     */
    private function transformNumeric($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Handle numeric strings and Excel numeric values
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Try to extract number from string (e.g., "$1,234.56" -> 1234.56)
        $cleaned = preg_replace('/[^0-9.-]/', '', (string) $value);
        return $cleaned !== '' && is_numeric($cleaned) ? (float) $cleaned : null;
    }

    /**
     * Transform Excel date values to Carbon date objects
     */
    private function transformDate($value)
    {
        if (!$value || $value === '') {
            return null;
        }

        try {
            // Check if it's numeric (Excel date serial number)
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value);
            }
            
            // Try parsing string date
            if (is_string($value)) {
                return \Carbon\Carbon::parse($value);
            }
        } catch (\Exception $e) {
            // Return null if date parsing fails
            return null;
        }

        return null;
    }

    /**
     * Optional validation rules for imported data
     */
    public function rules(): array
    {
        return [
            // Add validation rules if needed
            // 'unit_id' => 'nullable|string|max:255',
            // 'serial_number' => 'nullable|string|max:255|unique:products,serial_number',
        ];
    }
}
