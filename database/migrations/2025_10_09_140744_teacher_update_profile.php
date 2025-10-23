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
        Schema::table('teachers', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')
                ->constrained(table: 'users');
            $table->text('description')
                ->nullable()
                ->after('name');
            $table->string('picture')
                ->nullable()
                ->after('description');
        });

        Schema::create('curriculums', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained(
                table: 'teachers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('title')
            ->nullable();
            $table->integer('order')
            ->default(0);
            $table->timestamps();

        });

        Schema::create('social_medias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained(
                table: 'teachers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->enum('type', ['facebook', 'twitter', 'linkedin', 'tiktok', 'instagram', 'youtube']);
            $table->string('username');
            $table->timestamps();


        });





    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_medias');
        Schema::dropIfExists('curriculums');
        Schema::table('teachers', function (Blueprint $table): void {
            $table->dropColumn(['description', 'picture']);
        });
    }
};
