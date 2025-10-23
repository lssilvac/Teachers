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
        Schema::table('teachers', function (Blueprint $table) {
            /**
             * ===========================
             * CAMPOS EDITÁVEIS (usuário)
             * ===========================
             * Esses são mostrados para o professor e podem ser alterados.
             */
            $table->string('street_number', 20)->nullable()->after('age');      // Número
            $table->string('route', 255)->nullable()->after('street_number');  // Rua / Avenida
            $table->string('sublocality_level_1', 255)->nullable()->after('route'); // Bairro
            $table->string('locality', 255)->nullable()->after('sublocality_level_1'); // Cidade
            $table->string('administrative_area_level_1', 100)->nullable()->after('locality'); // Estado / UF
            $table->string('country', 2)->nullable()->after('administrative_area_level_1'); // Código ISO-2
            $table->string('postal_code', 20)->nullable()->after('country');   // CEP

            /**
             * ===========================
             * CAMPOS DE CONTROLE INTERNO
             * ===========================
             * Usados para integrações, não devem ser editados pelo professor.
             */
            $table->string('place_id', 255)->nullable()->after('postal_code'); // ID único do Google Maps
            $table->string('formatted_address', 500)->nullable()->after('place_id'); // Endereço completo pronto
            $table->decimal('latitude', 10, 7)->nullable()->after('formatted_address'); // Coordenadas
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // Remove todos os campos criados no up()
            $table->dropColumn([
                'street_number',
                'route',
                'sublocality_level_1',
                'locality',
                'administrative_area_level_1',
                'country',
                'postal_code',
                'place_id',
                'formatted_address',
                'latitude',
                'longitude',
            ]);
        });
    }
};
