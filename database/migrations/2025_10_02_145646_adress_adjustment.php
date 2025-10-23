<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('administrative_area_level_2')
            ->nullable()
            ->after('administrative_area_level_1');
            $table->string('google_search')
            ->nullable()
                ->after('longitude');
            ;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('administrative_area_level_2');
            $table->dropColumn('google_search');

            ;
        }
        );
    }
};

