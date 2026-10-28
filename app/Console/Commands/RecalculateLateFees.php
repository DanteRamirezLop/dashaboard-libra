<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Delay;
use App\Loan;
use App\Transaction;

class RecalculateLateFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:recalculateLateFees {--dry-run : Muestra los cambios sin guardarlos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula las moras con estado "late" usando la formula de cuota completa (getQuote) en lugar de mount_quota + initial';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        DB::beginTransaction();
        try {
            $delays = Delay::where('status', 'late')
                ->with('paymentSchedule')
                ->get();

            $totalDiff = 0;
            $affected = 0;

            foreach ($delays as $delay) {
                $paymentSchedule = $delay->paymentSchedule;
                if (!$paymentSchedule) {
                    $this->warn("Delay #{$delay->id}: sin payment_schedule asociado, se omite.");
                    continue;
                }

                $newDailyRate = $paymentSchedule->getQuote() * 0.00111;
                $newLateAmount = round($newDailyRate * $delay->days_late, 2);
                $diff = round($newLateAmount - $delay->late_amount, 2);

                if ($diff == 0) {
                    continue;
                }

                $this->line("Delay #{$delay->id} (loan #{$delay->loan_id}): {$delay->late_amount} -> {$newLateAmount} (diff {$diff})");

                if (!$dryRun) {
                    $loan = Loan::find($delay->loan_id);
                    $transaction = $loan ? Transaction::find($loan->transaction_id) : null;
                    if ($transaction) {
                        $transaction->final_total += $diff;
                        $transaction->additional_expense_value_2 += $diff;
                        $transaction->save();
                    } else {
                        $this->warn("Delay #{$delay->id}: transaccion del prestamo #{$delay->loan_id} no encontrada, se omite ajuste de transaccion.");
                    }

                    $delay->late_amount = $newLateAmount;
                    $delay->save();
                }

                $totalDiff += $diff;
                $affected++;
            }

            $this->info("Moras afectadas: {$affected}. Ajuste total: {$totalDiff}.");

            if ($dryRun) {
                $this->info('Modo dry-run: no se guardaron cambios.');
                DB::rollBack();
            } else {
                DB::commit();
                Log::info("pos:recalculateLateFees ejecutado. Moras afectadas: {$affected}. Ajuste total: {$totalDiff}.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
