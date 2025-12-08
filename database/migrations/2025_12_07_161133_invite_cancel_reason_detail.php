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
        Schema::table('invites', function (Blueprint $table) {
            $table->renameColumn('reason', 'cancel_reason_notes');

            $table->enum('cancel_reason', ['schedule_conflict', 'travel_impossible', 'health_issues', 'other',])
                ->nullable()
                ->after('canceled_by');




        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invites', function (Blueprint $table) {
            $table->renameColumn('cancel_reason_notes', 'reason');
            $table->dropColumn('cancel_reason');

        });
    }
};
