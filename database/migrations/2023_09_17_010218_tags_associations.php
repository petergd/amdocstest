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
        if (!Schema::hasTable('product_tag_associations')) {
			Schema::create('product_tag_associations', function (Blueprint $table) {
				$table->engine = 'InnoDB';
				$table->charset = 'utf8mb4';
				$table->collation = 'utf8mb4_unicode_ci';
				$table->comment('Product Tag Associations Table');
				$table->unsignedBigInteger('product_id');
				$table->unsignedBigInteger('tag_id');
				$table->primary(['product_id', 'tag_id']);
				$table->index('product_id');
				$table->index('tag_id');
				$table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
				$table->foreign('tag_id')->references('id')->on('product_tags')->cascadeOnUpdate()->cascadeOnDelete();
			});
		}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_tag_associations');
    }
};
