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
        // Escolas
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Turmas
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('school_id')
                ->nullable()
                ->constrained('schools')
                ->nullOnDelete();

            $table->foreignId('class_type_id')
                ->nullable()
                ->constrained('class_types')
                ->nullOnDelete();

            $table->string('name');
            $table->json('weekday')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        // Ano letivo
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')
                ->nullable()
                ->constrained('classes')
                ->nullOnDelete();

            $table->foreignId('subject_id')
                ->nullable()
                ->constrained('subjects')
                ->nullOnDelete();

            $table->timestamps();
        });

        // Convites
        Schema::create('invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')
                ->nullable()
                ->constrained('teachers')
                ->nullOnDelete();

            $table->foreignId('school_year_id')
                ->nullable()
                ->constrained('school_years')
                ->nullOnDelete();

            $table->enum('status', ['pending', 'accepted', 'rejected', 'canceled'])
                ->default('pending');

            $table->foreignId('canceled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('reason')->nullable();

            $table->timestamps();
        });

        // Datas dos convites
        Schema::create('invite_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invite_id')
                ->nullable()
                ->constrained('invites')
                ->nullOnDelete();

            $table->date('date')->nullable();
            $table->timestamps();
        });

        // Bloqueios de calendário
        Schema::create('calendar_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')
                ->nullable()
                ->constrained('teachers')
                ->nullOnDelete();

            $table->string('tag')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_locks');
        Schema::dropIfExists('invite_dates');
        Schema::dropIfExists('invites');
        Schema::dropIfExists('school_years');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('schools');
    }
};
