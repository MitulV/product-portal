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
      'Switch' => new SwitchImport(),
      'Docking Stations' => new DockingStationsImport(),
      'Other' => new OtherImport(),
    ];
  }
}

// Base trait for common import functionality
trait ProductImportTrait
{
  /**
   * Clean string values - trim whitespace and convert empty strings to null
   */
  protected function cleanValue($value)
  {
    if ($value === null) {
      return null;
    }

    $cleaned = trim((string) $value);
    return $cleaned === '' ? null : $cleaned;
  }

  /**
   * Transform numeric values for cost and numeric fields
   */
  protected function transformNumeric($value)
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
   * Transform integer values
   */
  protected function transformInteger($value)
  {
    $numeric = $this->transformNumeric($value);
    return $numeric !== null ? (int) $numeric : null;
  }

  /**
   * Transform Excel date values to Carbon date objects
   */
  protected function transformDate($value)
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
}

class GeneratorsImport implements ToModel, WithStartRow, WithValidation
{
  use ProductImportTrait;

  /**
   * Headers are at row 1, data starts at row 2
   */
  public function startRow(): int
  {
    return 2;
  }

  /**
   * Map Excel columns to product fields for Generators
   * Note: Column indices need to be adjusted based on actual Excel file structure
   */
  public function model(array $row)
  {
    // Skip completely empty rows
    if (!array_filter($row, function ($value) {
      return $value !== null && $value !== '';
    })) {
      return null;
    }

    // Map columns based on Generators sheet structure
    // Adjust indices based on actual Excel column positions
    return new Product([
      'product_type' => 'Generators',
      'hold_status' => $this->cleanValue($row[0] ?? null),
      'hold_branch' => $this->transformInteger($row[1] ?? null),
      'salesman' => $this->cleanValue($row[2] ?? null),
      'opportunity_name' => $this->cleanValue($row[3] ?? null),
      'brand' => $this->cleanValue($row[4] ?? null),
      'model_number' => $this->cleanValue($row[5] ?? null),
      'location' => $this->cleanValue($row[6] ?? null),
      'ipas_cpq_number' => $this->cleanValue($row[7] ?? null),
      'cps_po_number' => $this->cleanValue($row[8] ?? null),
      'enclosure' => $this->cleanValue($row[9] ?? null),
      'enclosure_type' => $this->cleanValue($row[10] ?? null),
      'tank' => $this->cleanValue($row[11] ?? null),
      'controller_series' => $this->cleanValue($row[12] ?? null),
      'breakers' => $this->cleanValue($row[13] ?? null),
      'notes' => $this->cleanValue($row[14] ?? null),
      'application_group' => $this->cleanValue($row[15] ?? null),
      'engine_model' => $this->cleanValue($row[16] ?? null),
      'unit_specification' => $this->cleanValue($row[17] ?? null),
      'ibc_certification' => $this->cleanValue($row[18] ?? null),
      'exhaust_emissions' => $this->cleanValue($row[19] ?? null),
      'temp_rise' => $this->cleanValue($row[20] ?? null),
      'description' => $this->cleanValue($row[21] ?? null),
      'fuel_type' => $this->cleanValue($row[22] ?? null),
      'voltage' => $this->transformInteger($row[23] ?? null),
      'phase' => $this->transformInteger($row[24] ?? null),
      'serial_number' => $this->cleanValue($row[25] ?? null),
      'unit_id' => $this->cleanValue($row[26] ?? null),
      'power' => $this->transformInteger($row[27] ?? null),
      'engine_speed' => $this->transformInteger($row[28] ?? null),
      'radiator_design_temp' => $this->transformInteger($row[29] ?? null),
      'frequency' => $this->transformInteger($row[30] ?? null),
      'full_load_amps' => $this->transformInteger($row[31] ?? null),
      'tech_spec' => $this->cleanValue($row[32] ?? null),
      'date_hold_added' => $this->transformDate($row[33] ?? null),
      'hold_expiration_date' => $this->transformDate($row[34] ?? null),
      'est_completion_date' => $this->transformDate($row[35] ?? null),
      'ship_date' => $this->transformDate($row[36] ?? null),
      'total_cost' => $this->transformNumeric($row[37] ?? null),
      'retail_cost' => $this->transformNumeric($row[38] ?? null),
      'tariff_cost' => $this->transformNumeric($row[39] ?? null),
      'sales_order_number' => $this->transformInteger($row[40] ?? null),
      'kw' => $this->transformNumeric($row[41] ?? null),
    ]);
  }

  public function rules(): array
  {
    return [];
  }
}

class SwitchImport implements ToModel, WithStartRow, WithValidation
{
  use ProductImportTrait;

  public function startRow(): int
  {
    return 2;
  }

  public function model(array $row)
  {
    // Skip completely empty rows
    if (!array_filter($row, function ($value) {
      return $value !== null && $value !== '';
    })) {
      return null;
    }

    return new Product([
      'product_type' => 'Switch',
      'hold_status' => $this->cleanValue($row[0] ?? null),
      'hold_branch' => $this->transformInteger($row[1] ?? null),
      'salesman' => $this->cleanValue($row[2] ?? null),
      'hold_expiration_date' => $this->transformDate($row[3] ?? null),
      'location' => $this->cleanValue($row[4] ?? null),
      'brand' => $this->cleanValue($row[5] ?? null),
      'transition_type' => $this->cleanValue($row[6] ?? null),
      'enclosure_type' => $this->cleanValue($row[7] ?? null),
      'bypass_isolation' => $this->cleanValue($row[8] ?? null),
      'service_entrance_rated' => $this->cleanValue($row[9] ?? null),
      'contactor_type' => $this->cleanValue($row[10] ?? null),
      'controller_model' => $this->cleanValue($row[11] ?? null),
      'communications_type' => $this->cleanValue($row[12] ?? null),
      'accessories' => $this->cleanValue($row[13] ?? null),
      'catalog_number' => $this->cleanValue($row[14] ?? null),
      'serial_number' => $this->cleanValue($row[15] ?? null),
      'quote_number' => $this->cleanValue($row[16] ?? null),
      'number_of_poles' => $this->cleanValue($row[17] ?? null),
      'description' => $this->cleanValue($row[18] ?? null),
      'amperage' => $this->transformInteger($row[19] ?? null),
      'voltage' => $this->transformInteger($row[20] ?? null),
      'phase' => $this->transformInteger($row[21] ?? null),
      'unit_id' => $this->cleanValue($row[22] ?? null),
      'date_hold_added' => $this->transformDate($row[23] ?? null),
      'est_completion_date' => $this->transformDate($row[24] ?? null),
      'retail_cost' => $this->transformNumeric($row[25] ?? null),
      'total_cost' => $this->transformNumeric($row[26] ?? null),
    ]);
  }

  public function rules(): array
  {
    return [];
  }
}

class DockingStationsImport implements ToModel, WithStartRow, WithValidation
{
  use ProductImportTrait;

  public function startRow(): int
  {
    return 2;
  }

  public function model(array $row)
  {
    // Skip completely empty rows
    if (!array_filter($row, function ($value) {
      return $value !== null && $value !== '';
    })) {
      return null;
    }

    return new Product([
      'product_type' => 'Docking Stations',
      'hold_status' => $this->cleanValue($row[0] ?? null),
      'hold_branch' => $this->transformInteger($row[1] ?? null),
      'salesman' => $this->cleanValue($row[2] ?? null),
      'hold_expiration_date' => $this->transformDate($row[3] ?? null),
      'location' => $this->cleanValue($row[4] ?? null),
      'brand' => $this->cleanValue($row[5] ?? null),
      'enclosure_type' => $this->cleanValue($row[6] ?? null),
      'contactor_type' => $this->cleanValue($row[7] ?? null),
      'accessories' => $this->cleanValue($row[8] ?? null),
      'catalog_number' => $this->cleanValue($row[9] ?? null),
      'serial_number' => $this->cleanValue($row[10] ?? null),
      'quote_number' => $this->cleanValue($row[11] ?? null),
      'circuit_breaker_type' => $this->cleanValue($row[12] ?? null),
      'description' => $this->cleanValue($row[13] ?? null),
      'amperage' => $this->transformInteger($row[14] ?? null),
      'voltage' => $this->transformInteger($row[15] ?? null),
      'phase' => $this->transformInteger($row[16] ?? null),
      'unit_id' => $this->cleanValue($row[17] ?? null),
      'date_hold_added' => $this->transformDate($row[18] ?? null),
      'est_completion_date' => $this->transformDate($row[19] ?? null),
      'retail_cost' => $this->transformNumeric($row[20] ?? null),
      'total_cost' => $this->transformNumeric($row[21] ?? null),
    ]);
  }

  public function rules(): array
  {
    return [];
  }
}

class OtherImport implements ToModel, WithStartRow, WithValidation
{
  use ProductImportTrait;

  public function startRow(): int
  {
    return 2;
  }

  public function model(array $row)
  {
    // Skip completely empty rows
    if (!array_filter($row, function ($value) {
      return $value !== null && $value !== '';
    })) {
      return null;
    }

    return new Product([
      'product_type' => 'Other',
      'hold_status' => $this->cleanValue($row[0] ?? null),
      'hold_branch' => $this->transformInteger($row[1] ?? null),
      'salesman' => $this->cleanValue($row[2] ?? null),
      'location' => $this->cleanValue($row[3] ?? null),
      'brand' => $this->cleanValue($row[4] ?? null),
      'serial_number' => $this->cleanValue($row[5] ?? null),
      'description' => $this->cleanValue($row[6] ?? null),
      'unit_id' => $this->cleanValue($row[7] ?? null),
      'hold_expiration_date' => $this->transformDate($row[8] ?? null),
      'date_hold_added' => $this->transformDate($row[9] ?? null),
      'retail_cost' => $this->transformNumeric($row[10] ?? null),
      'total_cost' => $this->transformNumeric($row[11] ?? null),
      'title' => $this->cleanValue($row[12] ?? null),
    ]);
  }

  public function rules(): array
  {
    return [];
  }
}
