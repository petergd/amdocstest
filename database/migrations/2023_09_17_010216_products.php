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
        if (!Schema::hasTable('products')) {
			Schema::create('products', function (Blueprint $table) {
				$table->engine = 'InnoDB';
				$table->charset = 'utf8mb4';
				$table->collation = 'utf8mb4_unicode_ci';
				$table->comment('Products Table');
				$table->id();
				$table->string('name');
				$table->string('code')->unique();
				$table->unsignedBigInteger('category_id');
				$table->float('price');
				$table->dateTime('release_date', $precision = 0);
				$table->index('category_id');
				$table->foreign('category_id')->references('id')->on('product_categories')->cascadeOnUpdate()->cascadeOnDelete();
			});
		}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
