<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // transaction_payment_id solo tiene sentido cuando la versión del cronograma
    // se generó por un pago (ej. pago a capital). Una reprogramación de fechas
    // no está ligada a ningún pago, así que la columna debe admitir NULL.
    public function up(): void
    {
        DB::statement('ALTER TABLE schedule_versions MODIFY COLUMN transaction_payment_id INT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE schedule_versions MODIFY COLUMN transaction_payment_id INT UNSIGNED NOT NULL');
    }
};
