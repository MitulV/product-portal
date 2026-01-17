<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
    protected $fillable = [
        'product_type',
        'hold_status',
        'hold_branch',
        'salesman',
        'opportunity_name',
        'location',
        'hold_expiration_date',
        'date_hold_added',
        'brand',
        'model_number',
        'est_completion_date',
        'total_cost',
        'tariff_cost',
        'retail_cost',
        'sales_order_number',
        'ipas_cpq_number',
        'cps_po_number',
        'ship_date',
        'voltage',
        'phase',
        'enclosure',
        'enclosure_type',
        'tank',
        'controller_series',
        'breakers',
        'serial_number',
        'unit_id',
        'notes',
        'description',
        'tech_spec',
        // Generators specific
        'application_group',
        'engine_model',
        'unit_specification',
        'ibc_certification',
        'exhaust_emissions',
        'temp_rise',
        'fuel_type',
        'power',
        'engine_speed',
        'radiator_design_temp',
        'frequency',
        'full_load_amps',
        // Switch specific
        'transition_type',
        'bypass_isolation',
        'service_entrance_rated',
        'contactor_type',
        'controller_model',
        'communications_type',
        'accessories',
        'catalog_number',
        'quote_number',
        'number_of_poles',
        'amperage',
        // Docking Stations specific
        'circuit_breaker_type',
    ];

    protected $casts = [
        'hold_expiration_date' => 'date',
        'date_hold_added' => 'date',
        'est_completion_date' => 'date',
        'ship_date' => 'date',
        'total_cost' => 'decimal:2',
        'tariff_cost' => 'decimal:2',
        'retail_cost' => 'decimal:2',
    ];

    public function galleries()
    {
        return $this->hasMany(ProductGallery::class);
    }

    /**
     * Get public fields for the product type
     * Based on the data dictionary: Public fields are shown to customers, Private fields are not
     */
    public function getPublicFields(): array
    {
        $publicFields = [
            'Generators' => [
                'brand', 'model_number', 'enclosure', 'enclosure_type', 'tank',
                'controller_series', 'breakers', 'application_group', 'engine_model',
                'unit_specification', 'ibc_certification', 'exhaust_emissions', 'temp_rise',
                'description', 'fuel_type', 'voltage', 'phase', 'unit_id', 'power',
                'engine_speed', 'radiator_design_temp', 'frequency', 'full_load_amps',
                'tech_spec', 'est_completion_date', 'ship_date'
            ],
            'Switch' => [
                'brand', 'transition_type', 'enclosure_type', 'bypass_isolation',
                'service_entrance_rated', 'contactor_type', 'controller_model',
                'communications_type', 'accessories', 'catalog_number', 'number_of_poles',
                'description', 'amperage', 'voltage', 'phase', 'unit_id', 'est_completion_date'
            ],
            'Docking Stations' => [
                'brand', 'enclosure_type', 'contactor_type', 'accessories', 'catalog_number',
                'circuit_breaker_type', 'description', 'amperage', 'voltage', 'phase',
                'unit_id', 'est_completion_date'
            ],
            'Other' => [
                'brand', 'description', 'unit_id'
            ],
        ];

        return $publicFields[$this->product_type] ?? [];
    }

    /**
     * Check if a field is public for this product type
     */
    public function isPublicField(string $field): bool
    {
        return in_array($field, $this->getPublicFields());
    }

    /**
     * Get display label for a field
     */
    public function getFieldLabel(string $field): string
    {
        $labels = [
            'brand' => 'Brand',
            'model_number' => 'Model Number',
            'enclosure' => 'Enclosure',
            'enclosure_type' => 'Enclosure Type',
            'tank' => 'Tank',
            'controller_series' => 'Controller Series',
            'breakers' => 'Breakers',
            'application_group' => 'Application Group',
            'engine_model' => 'Engine Model',
            'unit_specification' => 'Unit Specification',
            'ibc_certification' => 'IBC Certification',
            'exhaust_emissions' => 'Exhaust Emissions',
            'temp_rise' => 'Temp Rise',
            'description' => 'Description',
            'fuel_type' => 'Fuel Type',
            'voltage' => 'Voltage',
            'phase' => 'Phase',
            'unit_id' => 'Stock ID',
            'power' => 'Power',
            'engine_speed' => 'Engine Speed',
            'radiator_design_temp' => 'Radiator Design Temp',
            'frequency' => 'Frequency',
            'full_load_amps' => 'Full Load Amps',
            'tech_spec' => 'Tech Spec',
            'est_completion_date' => 'Est. Completion Date',
            'ship_date' => 'Ship Date',
            'transition_type' => 'Transition Type',
            'bypass_isolation' => 'Bypass-Isolation',
            'service_entrance_rated' => 'Service Entrance Rated',
            'contactor_type' => 'Contactor Type',
            'controller_model' => 'Controller Model',
            'communications_type' => 'Communications Type',
            'accessories' => 'Accessories',
            'catalog_number' => 'Catalog Number',
            'number_of_poles' => 'Number of Poles',
            'amperage' => 'Amperage',
            'circuit_breaker_type' => 'Circuit Breaker Type',
        ];

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
}
