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
    Schema::table('products', function (Blueprint $table) {
      // Add product_type column
      $table->enum('product_type', ['Generators', 'Switch', 'Docking Stations', 'Other'])->nullable()->after('id');

      // Common fields that don't exist yet
      $table->string('location')->nullable()->after('opportunity_name');
      $table->text('description')->nullable()->after('notes');
      $table->date('date_hold_added')->nullable()->after('hold_expiration_date');
      $table->decimal('retail_cost', 10, 2)->nullable()->after('tariff_cost');

      // Generators specific fields
      $table->string('application_group', 255)->nullable();
      $table->string('engine_model', 255)->nullable();
      $table->string('unit_specification', 255)->nullable();
      $table->string('ibc_certification', 255)->nullable();
      $table->string('exhaust_emissions', 255)->nullable();
      $table->string('temp_rise', 3)->nullable();
      $table->string('fuel_type', 6)->nullable();
      $table->integer('power')->nullable();
      $table->integer('engine_speed')->nullable();
      $table->integer('radiator_design_temp')->nullable();
      $table->integer('frequency')->nullable();
      $table->integer('full_load_amps')->nullable();

      // Switch specific fields
      $table->string('transition_type', 8)->nullable();
      $table->string('bypass_isolation', 24)->nullable();
      $table->string('service_entrance_rated', 255)->nullable();
      $table->string('contactor_type', 255)->nullable();
      $table->string('controller_model', 255)->nullable();
      $table->string('communications_type', 255)->nullable();
      $table->string('accessories', 255)->nullable();
      $table->string('catalog_number', 255)->nullable();
      $table->string('quote_number', 255)->nullable();
      $table->string('number_of_poles', 12)->nullable();
      $table->integer('amperage')->nullable();

      // Docking Stations specific fields (some overlap with Switch, but keeping separate for clarity)
      $table->string('circuit_breaker_type', 255)->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('products', function (Blueprint $table) {
      // Drop all added columns
      $table->dropColumn([
        'product_type',
        'location',
        'description',
        'date_hold_added',
        'retail_cost',
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
        'circuit_breaker_type',
      ]);
    });
  }
};
