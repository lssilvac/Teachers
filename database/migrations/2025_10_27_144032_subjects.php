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
        Schema::create('class_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('class_type_id')->nullable()->constrained(
                table: 'class_types');
            $table->timestamps();
        });

        Schema::create('teachers_x_subjects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subject_id')->constrained(
                table: 'subjects')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained(
                table: 'teachers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers_x_subjects');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('class_types');
    }
};
