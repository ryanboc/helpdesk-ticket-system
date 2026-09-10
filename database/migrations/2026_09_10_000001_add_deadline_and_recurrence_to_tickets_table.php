<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->date('deadline_date')->nullable()->after('status')->index();
            $table->boolean('is_recurring')->default(false)->after('deadline_date');
            $table->string('recurrence_frequency')->nullable()->after('is_recurring');
            $table->unsignedSmallInteger('recurrence_interval')->default(1)->after('recurrence_frequency');
            $table->date('recurrence_next_at')->nullable()->after('recurrence_interval')->index();
            $table->date('recurrence_ends_at')->nullable()->after('recurrence_next_at');
            $table->foreignId('recurrence_source_id')->nullable()->after('recurrence_ends_at')->constrained('tickets')->nullOnDelete();
        });
        DB::table('tickets')->where('status', 'closed')->update(['status' => 'finished']);
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recurrence_source_id');
            $table->dropColumn(['deadline_date', 'is_recurring', 'recurrence_frequency', 'recurrence_interval', 'recurrence_next_at', 'recurrence_ends_at']);
        });
    }
};
