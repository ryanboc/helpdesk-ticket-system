<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('receives_ticket_summaries')->default(true)->after('is_admin');
            $table->string('ticket_summary_frequency')->default('daily')->after('receives_ticket_summaries');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['receives_ticket_summaries', 'ticket_summary_frequency']));
    }
};
