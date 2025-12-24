<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductGallery extends Model
{
    protected $fillable = [
        'product_id',
        'file_url',
        'file_type',
        'file_name',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

