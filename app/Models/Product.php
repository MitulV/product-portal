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
    'title',
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
    'kw',
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
    'kw' => 'decimal:2',
  ];

  /** Gallery items linked by Stock ID so they survive Excel import / product refresh. */
  public function galleries()
  {
    return $this->hasMany(ProductGallery::class, 'unit_id', 'unit_id');
  }

  /** Thumbnail shown on client product cards (one per product), linked by Stock ID. */
  public function thumbnail()
  {
    return $this->hasOne(ProductGallery::class, 'unit_id', 'unit_id')->where('file_type', 'thumbnail');
  }

  /**
   * Main-page product card title by type: Brand - kW/Amperage/Title - Product Type.
   */
  public function getCardTitleAttribute(): string
  {
    $brand = $this->brand ? trim($this->brand) : 'Product';

    if ($this->product_type === 'Generators') {
      if ($this->kw !== null) {
        $kwDisplay = (string) (int) round($this->kw, 0) . ' kW';
        return "{$brand} - {$kwDisplay} - Generator";
      }
      return "{$brand} - Generator";
    }

    if ($this->product_type === 'Switch') {
      $amp = $this->amperage !== null ? (string) $this->amperage : '';
      return $amp !== '' ? "{$brand} - {$amp} - Transfer Switch" : "{$brand} - Transfer Switch";
    }

    if ($this->product_type === 'Docking Stations') {
      $amp = $this->amperage !== null ? (string) $this->amperage : '';
      return $amp !== '' ? "{$brand} - {$amp} - Docking Station" : "{$brand} - Docking Station";
    }

    if ($this->product_type === 'Other') {
      $title = $this->title ? trim($this->title) : '';
      return $title !== '' ? "{$brand} - {$title}" : $brand;
    }

    return $brand;
  }

  /**
   * URL slug segment: lowercase, alphanumeric and dashes only (for pretty URLs).
   */
  public static function slugify(?string $value): string
  {
    if ($value === null || trim($value) === '') {
      return '';
    }
    $slug = preg_replace('/[^a-z0-9]+/i', '-', trim($value));
    $slug = trim($slug, '-');
    return strtolower($slug);
  }

  /**
   * Product type as URL slug (e.g. Generators -> generator).
   */
  public function getProductTypeSlug(): string
  {
    $map = [
      'Generators' => 'generator',
      'Switch' => 'transfer-switch',
      'Docking Stations' => 'docking-station',
      'Other' => 'other',
    ];
    return $map[$this->product_type] ?? self::slugify($this->product_type);
  }

  /**
   * Single hyphenated slug for pretty URL: product-type-brand-kw-voltage-enclosure.
   * e.g. generator-mtu-100kw-480v-lvl3
   */
  public function getShowSlug(): string
  {
    $parts = [];
    $parts[] = $this->getProductTypeSlug();
    $brand = self::slugify($this->brand);
    if ($brand !== '') {
      $parts[] = $brand;
    }
    if ($this->kw !== null) {
      $parts[] = (string) (int) round($this->kw, 0) . 'kw';
    }
    if ($this->voltage !== null && (string) $this->voltage !== '') {
      $v = preg_replace('/\D/', '', (string) $this->voltage);
      if ($v !== '') {
        $parts[] = $v . 'v';
      }
    }
    $enclosure = self::slugify($this->enclosure ?? $this->enclosure_type ?? null);
    if ($enclosure !== '') {
      $parts[] = $enclosure;
    }
    return implode('-', $parts);
  }

  /**
   * Route parameters for products.show: id + optional hyphenated slug.
   * e.g. /products/18/generator-mtu-100kw-480v-lvl3
   */
  public function showRouteParameters(): array
  {
    $slug = $this->getShowSlug();
    $params = ['product' => $this];
    if ($slug !== '') {
      $params['slug'] = $slug;
    }
    return $params;
  }

  /**
   * Get public fields for the product type
   * Based on the data dictionary: Public fields are shown to customers, Private fields are not
   */
  public function getPublicFields(): array
  {
    $publicFields = [
      'Generators' => [
        'brand',
        'model_number',
        'enclosure',
        'enclosure_type',
        'tank',
        'controller_series',
        'breakers',
        'application_group',
        'engine_model',
        'unit_specification',
        'ibc_certification',
        'exhaust_emissions',
        'temp_rise',
        'description',
        'fuel_type',
        'voltage',
        'phase',
        'unit_id',
        'power',
        'engine_speed',
        'radiator_design_temp',
        'frequency',
        'full_load_amps',
        'kw',
        'est_completion_date',
        'ship_date'
      ],
      'Switch' => [
        'brand',
        'transition_type',
        'enclosure_type',
        'bypass_isolation',
        'service_entrance_rated',
        'contactor_type',
        'controller_model',
        'communications_type',
        'accessories',
        'catalog_number',
        'number_of_poles',
        'description',
        'amperage',
        'voltage',
        'phase',
        'unit_id',
        'est_completion_date'
      ],
      'Docking Stations' => [
        'brand',
        'enclosure_type',
        'contactor_type',
        'accessories',
        'catalog_number',
        'circuit_breaker_type',
        'description',
        'amperage',
        'voltage',
        'phase',
        'unit_id',
        'est_completion_date'
      ],
      'Other' => [
        'brand',
        'description',
        'title',
        'unit_id'
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
      'title' => 'Title',
      'fuel_type' => 'Fuel Type',
      'voltage' => 'Voltage',
      'phase' => 'Phase',
      'unit_id' => 'Stock ID',
      'power' => 'Power',
      'engine_speed' => 'Engine Speed',
      'radiator_design_temp' => 'Radiator Design Temp',
      'frequency' => 'Frequency',
      'full_load_amps' => 'Full Load Amps',
      'kw' => 'kW',
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
