<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Guarda el último día en que se cobró la mora diaria, para que
 * pos:calculateStatusLoan no cobre dos veces el mismo día (idempotencia).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('delays', 'last_charged_date')) {
            Schema::table('delays', function (Blueprint $table) {
                $table->date('last_charged_date')->nullable()->after('days_late');
            });
        }

        // Moras activas: se asume que su última modificación fue el último cobro diario.
        DB::table('delays')
            ->where('status', 'late')
            ->whereNull('last_charged_date')
            ->update(['last_charged_date' => DB::raw('DATE(updated_at)')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('delays', 'last_charged_date')) {
            Schema::table('delays', function (Blueprint $table) {
                $table->dropColumn('last_charged_date');
            });
        }
    }
};
