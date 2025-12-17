<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ProductImport implements ToModel, WithHeadingRow, WithMultipleSheets
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip if relevant key fields are empty, or handle gracefully
        // The user specified 'Generators' sheet and columns A5 to Y5.
        // WithHeadingRow usually handles header detection if it's the first row of the selection.
        // However, user said "rows can be variable numbers" starting from A5.
        // If the header is at row 4 or 5, we might need to configure heading row.
        // Assuming standard format where headers are top of the dataset.
        
        // Mapping Excel columns to DB columns
        // 'hold_status' => $row['hold_status'], etc.
        
        return Product::updateOrCreate(
            ['unit_id' => $row['unit_id'] ?? null], // Match by Unit ID ? Or Serial?
            // User requested "refresh and update", so updateOrCreate is good.
            // If unit_id is missing, we might create duplicates if we don't have another key.
            // Let's assume unit_id is key. If not, serial_number.
            
            [
                'hold_status' => $row['hold_status'] ?? null,
                'hold_branch' => $row['hold_branch'] ?? null,
                'salesman' => $row['salesman'] ?? null,
                'opportunity_name' => $row['opportunity_name'] ?? null,
                'hold_expiration_date' => $this->transformDate($row['hold_expiration_date'] ?? null),
                'brand' => $row['brand'] ?? null,
                'model_number' => $row['model_number'] ?? null,
                'est_completion_date' => $this->transformDate($row['est_completion_date'] ?? null),
                'total_cost' => $row['total_cost'] ?? null,
                'tariff_cost' => $row['tariff_cost'] ?? null,
                'sales_order_number' => $row['sales_order_number'] ?? null,
                'ipas_cpq_number' => $row['ipas_cpq_number'] ?? null,
                'cps_po_number' => $row['cps_po_number'] ?? null,
                'ship_date' => $this->transformDate($row['ship_date'] ?? null),
                'voltage' => $row['voltage'] ?? null,
                'phase' => $row['phase'] ?? null,
                'enclosure' => $row['enclosure'] ?? null,
                'enclosure_type' => $row['enclosure_type'] ?? null,
                'tank' => $row['tank'] ?? null,
                'controller_series' => $row['controller_series'] ?? null,
                'breakers' => $row['breakers'] ?? null,
                'serial_number' => $row['serial_number'] ?? null,
               // 'unit_id' is in match array
                'notes' => $row['notes'] ?? null,
                'tech_spec' => $row['tech_spec'] ?? null,
            ]
        );
    }

    public function sheets(): array
    {
        return [
            'Generators' => $this,
        ];
    }
    
    // Helper to transform Excel dates
    private function transformDate($value)
    {
        if (empty($value)) return null;
        try {
            return Date::excelToDateTimeObject($value);
        } catch (\Exception $e) {
            return null; // or parse string if it's text
        }
    }
}
