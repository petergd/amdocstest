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
        if (!Schema::hasTable('product_categories')) {
			Schema::create('product_categories', function (Blueprint $table) {
				$table->engine = 'InnoDB';
				$table->charset = 'utf8mb4';
				$table->collation = 'utf8mb4_unicode_ci';
				$table->comment('Product Categories Table');
				$table->id();
				$table->string('name');
				$table->index('name');
			});
		}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
