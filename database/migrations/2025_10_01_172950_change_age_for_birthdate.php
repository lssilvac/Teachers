<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Sobe a mudança
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // Data de nascimento (deixe nullable para não travar cadastro antigo)
            $table->date('birth_date')->nullable()->after('name');

            // Remover a coluna age (se existir)
            if (Schema::hasColumn('teachers', 'age')) {
                $table->dropColumn('age');
            }
        });
    }

    // Desfaz a mudança
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // Recria a coluna age (como era um campo simples de idade)
            $table->unsignedTinyInteger('age')->nullable()->after('name');

            // Remove a birth_date
            if (Schema::hasColumn('teachers', 'birth_date')) {
                $table->dropColumn('birth_date');
            }
        });
    }
};
