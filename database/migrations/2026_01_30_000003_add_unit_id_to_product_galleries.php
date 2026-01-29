<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_galleries', function (Blueprint $table) {
            $table->string('unit_id', 255)->nullable()->after('product_id');
        });

        // Backfill unit_id from products so existing gallery rows stay linked by Stock ID
        DB::statement('
            UPDATE product_galleries
            SET unit_id = (SELECT unit_id FROM products WHERE products.id = product_galleries.product_id LIMIT 1)
            WHERE product_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('product_galleries', function (Blueprint $table) {
            $table->dropColumn('unit_id');
        });
    }
};
