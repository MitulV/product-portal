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
    Schema::table('product_galleries', function (Blueprint $table) {
      // Drop the foreign key constraint to make tables independent
      $table->dropForeign(['product_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('product_galleries', function (Blueprint $table) {
      // Re-add the foreign key constraint
      $table->foreign('product_id')
        ->references('id')
        ->on('products')
        ->onDelete('cascade');
    });
  }
};
