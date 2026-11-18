<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Corrige refinanciamientos anteriores: refinanceStore solo marcaba como 'refinanced'
 * las cuotas 'pending' y 'overdue', y las 'partial' del cronograma viejo seguían
 * sumando en la "deuda vencida" aunque su saldo ya estaba en el nuevo cronograma.
 */
return new class extends Migration
{
    public function up(): void
    {
        $quotas = DB::table('payment_schedules')
            ->where('status', 'partial')
            ->whereNotNull('refinanced_at')
            ->get(['id', 'loan_id']);

        if ($quotas->isEmpty()) {
            return;
        }

        DB::table('payment_schedules')
            ->whereIn('id', $quotas->pluck('id'))
            ->update(['status' => 'refinanced']);

        Log::info('Migración: cuotas parciales de préstamos refinanciados marcadas como refinanced. '
            . 'Cuotas: ' . $quotas->pluck('id')->implode(',') . ' | Préstamos: ' . $quotas->pluck('loan_id')->unique()->implode(','));
    }

    public function down(): void
    {
        // Sin reversión automática: las cuotas corregidas quedan listadas en el log de la migración.
        // Revertir el refinanciamiento desde el listado restaura su estado 'partial'.
    }
};
