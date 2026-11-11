<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Denormalised effective price range so listing can filter/sort without joining variants.
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('min_price', 12, 2)->nullable()->after('sale_price')->index();
            $table->decimal('max_price', 12, 2)->nullable()->after('min_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['min_price', 'max_price']);
        });
    }
};
