<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'hold_status',
        'hold_branch',
        'salesman',
        'opportunity_name',
        'hold_expiration_date',
        'brand',
        'model_number',
        'est_completion_date',
        'total_cost',
        'tariff_cost',
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
        'tech_spec',
        'category_id',
    ];

    protected $casts = [
        'hold_expiration_date' => 'date',
        'est_completion_date' => 'date',
        'ship_date' => 'date',
        'total_cost' => 'decimal:2',
        'tariff_cost' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
