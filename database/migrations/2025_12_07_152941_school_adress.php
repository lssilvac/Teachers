<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('street_number', 20)->nullable()->after('name');
            $table->string('route', 255)->nullable()->after('street_number');
            $table->string('sublocality_level_1', 255)->nullable()->after('route');
            $table->string('locality', 255)->nullable()->after('sublocality_level_1');
            $table->string('administrative_area_level_1', 100)->nullable()->after('locality');
            $table->string('administrative_area_level_2', 255)->nullable()->after('administrative_area_level_1');
            $table->string('country', 2)->nullable()->after('administrative_area_level_2');
            $table->string('postal_code', 20)->nullable()->after('country');
            $table->string('place_id', 255)->nullable()->after('postal_code');
            $table->string('formatted_address', 255)->nullable()->after('place_id');
            $table->decimal('latitude', 10, 7)->nullable()->after('formatted_address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('google_search', 255)->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // drop sempre de baixo pra cima
            $table->dropColumn('google_search');
            $table->dropColumn('longitude');
            $table->dropColumn('latitude');
            $table->dropColumn('formatted_address');
            $table->dropColumn('place_id');
            $table->dropColumn('postal_code');
            $table->dropColumn('country');
            $table->dropColumn('administrative_area_level_2');
            $table->dropColumn('administrative_area_level_1');
            $table->dropColumn('locality');
            $table->dropColumn('sublocality_level_1');
            $table->dropColumn('route');
            $table->dropColumn('street_number');
        });
    }
};
