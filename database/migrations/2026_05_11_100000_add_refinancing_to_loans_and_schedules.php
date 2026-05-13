<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar parent_loan_id a loans
        Schema::table('loans', function (Blueprint $table) {
            $table->unsignedInteger('parent_loan_id')->nullable()->after('id');
        });

        // Ampliar ENUM status en loans para incluir 'refinanced'
        DB::statement("ALTER TABLE loans MODIFY COLUMN status ENUM('quotation','approved','in arrears','cancelled','paid','partial','refinanced') NOT NULL");

        // Ampliar ENUM status en payment_schedules para incluir 'refinanced'
        DB::statement("ALTER TABLE payment_schedules MODIFY COLUMN status ENUM('pending','paid','overdue','partial','refinanced') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payment_schedules MODIFY COLUMN status ENUM('pending','paid','overdue','partial') NOT NULL");
        DB::statement("ALTER TABLE loans MODIFY COLUMN status ENUM('quotation','approved','in arrears','cancelled','paid','partial') NOT NULL");

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('parent_loan_id');
        });
    }
};
