<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE product_galleries MODIFY COLUMN file_type ENUM('image', 'document', 'thumbnail') NOT NULL");
        }
        // SQLite and others: file_type is stored as string, so 'thumbnail' is already allowed
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE product_galleries MODIFY COLUMN file_type ENUM('image', 'document') NOT NULL");
        }
    }
};
