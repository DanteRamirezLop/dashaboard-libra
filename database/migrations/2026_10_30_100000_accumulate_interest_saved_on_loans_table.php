<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * loans.interest_saved guardaba solo el ahorro del último pago a capital, mientras que
     * discount_amount suma todos; con 2+ pagos a capital el "Total vencido" salía negativo.
     * Se recalcula como el acumulado de los descuentos de todos los pagos a capital.
     */
    public function up()
    {
        DB::statement("
            UPDATE loans l
            JOIN (
                SELECT sv.loan_id, SUM(pa.amount_discounted) AS total_saved
                FROM schedule_versions sv
                JOIN payment_applications pa
                    ON pa.transaction_payment_id = sv.transaction_payment_id
                    AND pa.payment_schedule_id IS NULL
                GROUP BY sv.loan_id
            ) x ON x.loan_id = l.id
            SET l.interest_saved = x.total_saved
        ");
    }

    public function down()
    {
        //
    }
};
