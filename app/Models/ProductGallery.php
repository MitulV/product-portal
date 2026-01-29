<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductGallery extends Model
{
    protected $fillable = [
        'product_id',
        'unit_id',
        'file_url',
        'file_type',
        'file_name',
    ];

    /** Link by product_id (may be stale after Excel import). */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Current product by Stock ID – stays correct after products are re-imported. */
    public function currentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'unit_id', 'unit_id');
    }
}

