<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('hold_status')->nullable();
            $table->string('hold_branch')->nullable();
            $table->string('salesman')->nullable();
            $table->string('opportunity_name')->nullable();
            $table->date('hold_expiration_date')->nullable();
            $table->string('brand')->nullable();
            $table->string('model_number')->nullable();
            $table->date('est_completion_date')->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->decimal('tariff_cost', 10, 2)->nullable();
            $table->string('sales_order_number')->nullable();
            $table->string('ipas_cpq_number')->nullable();
            $table->string('cps_po_number')->nullable();
            $table->date('ship_date')->nullable();
            $table->string('voltage')->nullable();
            $table->string('phase')->nullable();
            $table->string('enclosure')->nullable();
            $table->string('enclosure_type')->nullable();
            $table->string('tank')->nullable();
            $table->string('controller_series')->nullable();
            $table->string('breakers')->nullable();
            $table->string('serial_number')->unique()->nullable();
            $table->string('unit_id')->unique()->nullable();
            $table->text('notes')->nullable();
            $table->text('tech_spec')->nullable();
            // Retain category_id if still useful, otherwise remove. User didn't specify categories in new list but they are often useful.
            // Keeping it for now as a nullable foreign key.
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
