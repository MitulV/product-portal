<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class GenerateSampleExcel extends Command
{
  protected $signature = 'excel:generate-sample {--output=sample-products.xlsx} {--template : Generate empty template with headers only}';
  protected $description = 'Generate a sample Excel file with all 4 product type sheets for testing imports';

  public function handle()
  {
    $outputFile = $this->option('output');
    $isTemplate = $this->option('template');
    $spreadsheet = new Spreadsheet();

    // Remove default sheet
    $spreadsheet->removeSheetByIndex(0);

    // Create Generators sheet
    $this->createGeneratorsSheet($spreadsheet, $isTemplate);

    // Create Switch sheet
    $this->createSwitchSheet($spreadsheet, $isTemplate);

    // Create Docking Stations sheet
    $this->createDockingStationsSheet($spreadsheet, $isTemplate);

    // Create Other sheet
    $this->createOtherSheet($spreadsheet, $isTemplate);

    // Write to file
    $writer = new Xlsx($spreadsheet);

    // If output path is relative and doesn't start with ../, use storage/app
    // Otherwise use the path as-is (for absolute paths or ../public paths)
    if (strpos($outputFile, '../') === 0 || strpos($outputFile, '/') === 0 || preg_match('/^[A-Z]:/', $outputFile)) {
      $filePath = $outputFile;
    } else {
      $filePath = storage_path('app/' . $outputFile);
    }

    $writer->save($filePath);

    $message = $isTemplate
      ? "Empty template file created successfully at: {$filePath}"
      : "Sample Excel file created successfully at: {$filePath}";
    $this->info($message);
    return 0;
  }

  private function createGeneratorsSheet($spreadsheet, $isTemplate = false)
  {
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle('Generators');

    // Headers in row 1 (A1 onwards)
    $headers = [
      'Hold',
      'Hold Branch',
      'Salesman',
      'Opportunity Name',
      'Brand',
      'Model',
      'Location',
      'IPAS/CPQ #',
      'CPS PO#',
      'Enclosure',
      'Enclosure Type',
      'Tank',
      'Controller Series',
      'Breaker(s)',
      'Notes',
      'Application Group',
      'Engine Model',
      'Unit Specification',
      'IBC Certification',
      'Exhaust Emissions',
      'Temp Rise',
      'Description',
      'Fuel Type',
      'Voltage',
      'Phase',
      'Serial Number',
      'Stock ID',
      'Power',
      'Engine Speed',
      'Radiator Design Temp',
      'Frequency',
      'Full Load Amps',
      'Tech Spec',
      'Date Hold Added',
      'Hold Expiration',
      'Est Completion Date',
      'Ship Date',
      'Total Cost',
      'Retail Cost',
      'Tariff (Included in Total Cost)',
      'Sales Order #',
      'kW'
    ];

    // Set headers in row 1
    $sheet->fromArray([$headers], null, 'A1');

    // Style header row (A1:AP1 = columns A through AP, 42 columns)
    $headerStyle = [
      'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
      'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
      'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ];
    $sheet->getStyle('A1:AP1')->applyFromArray($headerStyle);

    // Sample data rows (starting from row 2) - only if not template
    if (!$isTemplate) {
      $sampleData = [
        [
          'Available',
          101,
          'John Smith',
          'Project Alpha',
          'mtu',
          'DS80',
          'Warehouse A',
          '338270921',
          'N903000912',
          'OPU',
          'No Encl',
          '12 Hr',
          '1500',
          '(2) 100 Amp and 50 Amp LSI 100%',
          'Test generator unit',
          'Standby',
          '12V2000',
          'Standard',
          'Yes',
          'Tier 4 Final',
          '40',
          'High-performance diesel generator with advanced control system',
          'Diesel',
          480,
          3,
          '95130502220',
          '216990',
          2000,
          1800,
          200,
          60,
          2400,
          'https://example.com/tech-spec.pdf',
          '2024-01-15',
          '2024-12-31',
          '2024-06-30',
          '2024-07-15',
          125000.00,
          135000.00,
          5000.00,
          1336998,
          2000
        ],
        [
          'Hold',
          102,
          'Jane Doe',
          'Project Beta',
          'cummins',
          'QSG12',
          'Warehouse B',
          '338270922',
          'N903000913',
          'NEMA 3R',
          'Weatherproof',
          '24 Hr',
          '2000',
          '(3) 200 Amp LSI',
          'Backup generator',
          'Prime',
          'QSK60',
          'Premium',
          'Yes',
          'Tier 4 Final',
          '50',
          'Commercial grade generator for industrial use',
          'Diesel',
          480,
          3,
          '95130502221',
          '216991',
          3000,
          1500,
          180,
          60,
          3600,
          'https://example.com/tech-spec2.pdf',
          '2024-02-01',
          '2025-01-31',
          '2024-08-15',
          '2024-09-01',
          185000.00,
          200000.00,
          8000.00,
          1336999,
          3000
        ],
      ];

      $row = 2; // Data starts from row 2 (row 1 is headers)
      foreach ($sampleData as $data) {
        $sheet->fromArray([$data], null, "A{$row}");
        $row++;
      }
    }

    // Auto-size columns (A to AP = 42 columns)
    for ($col = 0; $col < 42; $col++) {
      $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
    }
  }

  private function createSwitchSheet($spreadsheet, $isTemplate = false)
  {
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle('Switch');

    // Headers in row 1
    $headers = [
      'Hold',
      'Hold Branch',
      'Salesman',
      'Hold Expiration',
      'Location',
      'Brand',
      'Transition Type',
      'Enclosure Type',
      'Bypass-Isolation',
      'Service Entrance Rated',
      'Contactor Type',
      'Controller Model',
      'Communications Type',
      'Accessories',
      'Catalog Number',
      'Serial Number',
      'Quote Number',
      'Number of Poles',
      'Description',
      'Amperage',
      'Voltage',
      'Phase',
      'Stock ID',
      'Date Hold Added',
      'Est. Completion Date',
      'Retail Cost',
      'Total Cost'
    ];

    $sheet->fromArray([$headers], null, 'A1');

    // Style header row
    $headerStyle = [
      'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
      'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
      'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ];
    $sheet->getStyle('A1:AA1')->applyFromArray($headerStyle);

    // Sample data - only if not template
    if (!$isTemplate) {
      $sampleData = [
        [
          'Available',
          201,
          'Mike Johnson',
          '2024-12-31',
          'Warehouse C',
          'ASCO',
          'Closed',
          'NEMA 3R',
          'Bypass',
          'Yes',
          'Contactor A',
          'Model 7000',
          'Modbus',
          'Remote Monitoring',
          'ASCO-7000-400',
          'SW001234',
          'Q-2024-001',
          '3',
          'Automatic transfer switch for generator backup',
          400,
          480,
          3,
          'SW001',
          '2024-01-10',
          '2024-05-15',
          15000.00,
          18000.00
        ],
        [
          'Hold',
          202,
          'Sarah Williams',
          '2025-01-31',
          'Warehouse D',
          'Generac',
          'Open',
          'NEMA 4',
          'Isolation',
          'Yes',
          'Contactor B',
          'Model 5000',
          'Ethernet',
          'Weatherproof Enclosure',
          'GEN-5000-600',
          'SW001235',
          'Q-2024-002',
          '3',
          'High capacity transfer switch',
          600,
          480,
          3,
          'SW002',
          '2024-02-05',
          '2024-06-20',
          22000.00,
          25000.00
        ],
      ];

      $row = 2; // Data starts from row 2 (row 1 is headers)
      foreach ($sampleData as $data) {
        $sheet->fromArray([$data], null, "A{$row}");
        $row++;
      }
    }

    // Auto-size columns (A to AA = 27 columns)
    for ($col = 0; $col < 27; $col++) {
      $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
    }
  }

  private function createDockingStationsSheet($spreadsheet, $isTemplate = false)
  {
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle('Docking Stations');

    // Headers in row 1
    $headers = [
      'Hold',
      'Hold Branch',
      'Salesman',
      'Hold Expiration',
      'Location',
      'Brand',
      'Enclosure Type',
      'Contactor Type',
      'Accessories',
      'Catalog Number',
      'Serial Number',
      'Quote Number',
      'Circuit Breaker Type',
      'Description',
      'Amperage',
      'Voltage',
      'Phase',
      'Stock ID',
      'Date Hold Added',
      'Est. Completion Date',
      'Retail Cost',
      'Total Cost'
    ];

    $sheet->fromArray([$headers], null, 'A1');

    // Style header row
    $headerStyle = [
      'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
      'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
      'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ];
    $sheet->getStyle('A1:V1')->applyFromArray($headerStyle);

    // Sample data - only if not template
    if (!$isTemplate) {
      $sampleData = [
        [
          'Available',
          301,
          'Tom Brown',
          '2024-12-31',
          'Warehouse E',
          'Russelectric',
          'NEMA 3R',
          'Type A',
          'Remote Control',
          'RUS-DS-400',
          'DS001234',
          'Q-2024-003',
          'Molded Case',
          'Docking station for generator connection',
          400,
          480,
          3,
          'DS001',
          '2024-01-20',
          '2024-04-30',
          12000.00,
          14500.00
        ],
        [
          'Available',
          302,
          'Lisa Anderson',
          '2025-02-28',
          'Warehouse F',
          'Kohler',
          'NEMA 4',
          'Type B',
          'Digital Display',
          'KOH-DS-600',
          'DS001235',
          'Q-2024-004',
          'Air Circuit',
          'Heavy duty docking station',
          600,
          480,
          3,
          'DS002',
          '2024-02-15',
          '2024-05-31',
          18000.00,
          21000.00
        ],
      ];

      $row = 2; // Data starts from row 2 (row 1 is headers)
      foreach ($sampleData as $data) {
        $sheet->fromArray([$data], null, "A{$row}");
        $row++;
      }
    }

    // Auto-size columns (A to V = 22 columns)
    for ($col = 0; $col < 22; $col++) {
      $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
    }
  }

  private function createOtherSheet($spreadsheet, $isTemplate = false)
  {
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle('Other');

    // Headers in row 1
    $headers = [
      'Hold',
      'Hold Branch',
      'Salesman',
      'Location',
      'Brand',
      'Serial Number',
      'Description',
      'Stock ID',
      'Hold Expiration',
      'Date Hold Added',
      'Retail Cost',
      'Total Cost',
      'Title'
    ];

    $sheet->fromArray([$headers], null, 'A1');

    // Style header row (A1:M1 = 13 columns)
    $headerStyle = [
      'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
      'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
      'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ];
    $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

    // Sample data - only if not template
    if (!$isTemplate) {
      $sampleData = [
        [
          'Available',
          401,
          'Robert Taylor',
          'Warehouse G',
          'Generic',
          'OTH001234',
          'Miscellaneous equipment item',
          'OTH001',
          '2024-12-31',
          '2024-01-25',
          5000.00,
          6000.00,
          'Accessory Kit'
        ],
        [
          'Hold',
          402,
          'Emily Davis',
          'Warehouse H',
          'BrandX',
          'OTH001235',
          'Additional component for system',
          'OTH002',
          '2025-03-31',
          '2024-02-10',
          3500.00,
          4200.00,
          'Control Panel'
        ],
      ];

      $row = 2; // Data starts from row 2 (row 1 is headers)
      foreach ($sampleData as $data) {
        $sheet->fromArray([$data], null, "A{$row}");
        $row++;
      }
    }

    // Auto-size columns (A to M = 13 columns)
    for ($col = 0; $col < 13; $col++) {
      $sheet->getColumnDimensionByColumn($col + 1)->setAutoSize(true);
    }
  }
}
