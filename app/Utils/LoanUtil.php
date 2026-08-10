<?php

namespace App\Utils;

use App\Loan;
use Illuminate\Support\Facades\DB;

class LoanUtil
{
    /**
     * Base query used both by the loans DataTable (web) and the
     * dashboard API endpoint. Returns a query builder, not the results.
     */
    public function loanListQuery($business_id, array $filters = [])
    {
        $psAgg = DB::table('payment_schedules as ps')
            ->leftJoin('schedule_versions as sv', 'sv.id', '=', 'ps.schedule_version_id')
            ->selectRaw("
                ps.loan_id,
                COALESCE(SUM(
                    CASE WHEN ps.status NOT IN ('pending', 'refinanced')
                    THEN ps.mount_quota + ps.gps_quota + ps.sure_quota + ps.admin_fee_quota + ps.initial
                    ELSE 0 END
                ),0) as delay,

                COALESCE(SUM(
                    CASE WHEN ps.status = 'pending'
                    THEN ps.mount_quota + ps.gps_quota + ps.sure_quota + ps.admin_fee_quota + ps.initial
                    ELSE 0 END
                ),0) as for_due,

                MIN(CASE WHEN ps.status = 'pending' THEN ps.sheduled_date END) as next_due_date,
                MAX(ps.sheduled_date) as loan_end_date,
                SUBSTRING_INDEX(GROUP_CONCAT(
                    CASE WHEN ps.status = 'pending'
                    THEN ps.mount_quota + ps.gps_quota + ps.sure_quota + ps.admin_fee_quota + ps.initial
                    END ORDER BY ps.sheduled_date ASC
                ), ',', 1) as next_due_amount
            ")
            ->where(function ($q) {
                // Subquery correlacionado: ¿este loan_id tiene alguna versión activa?
                $activeVersionExists = function ($sq) {
                    $sq->selectRaw('1')
                        ->from('payment_schedules as psx')
                        ->join('schedule_versions as svx', 'svx.id', '=', 'psx.schedule_version_id')
                        ->whereColumn('psx.loan_id', 'ps.loan_id')
                        ->where('svx.status', 'active')
                        ->limit(1);
                };
                $q
                    // CASO A: Si NO existe versión activa => usa SOLO originales (NULL)
                    ->where(function ($q1) use ($activeVersionExists) {
                        $q1->whereNotExists($activeVersionExists)
                            ->whereNull('ps.schedule_version_id');
                    })
                    // CASO B: Si SÍ existe versión activa => usa SOLO filas de esa(s) versión(es) activa(s)
                    ->orWhere(function ($q2) use ($activeVersionExists) {
                        $q2->whereExists($activeVersionExists)
                            ->where('sv.status', 'active');
                    });
            })
            ->groupBy('ps.loan_id');

        $dAgg = DB::table('delays as d')
            ->selectRaw("
                d.loan_id,
                COALESCE(SUM(
                    CASE WHEN d.status = 'late'
                    THEN d.late_amount
                    ELSE 0 END
                ),0) as mora
            ")
            ->whereNull('d.deleted_at')
            ->groupBy('d.loan_id');

        return Loan::query()
            ->leftJoin('transactions', 'loans.transaction_id', '=', 'transactions.id')
            ->leftJoinSub($psAgg, 'psa', function ($join) {
                $join->on('psa.loan_id', '=', 'loans.id');
            })
            ->leftJoinSub($dAgg, 'da', function ($join) {
                $join->on('da.loan_id', '=', 'loans.id');
            })
            ->where('loans.business_id', $business_id)
            ->where('loans.status', '!=', 'quotation')
            ->when(empty($filters['include_all_types'] ?? null), function ($q) {
                $q->where('loans.type', '!=', 'rent-sale');
            })
            ->when(! empty($filters['service_staffs'] ?? null), function ($q) use ($filters) {
                $q->where('loans.waiter', $filters['service_staffs']);
            })
            ->when(! empty($filters['loan_list_filter_status'] ?? null), function ($q) use ($filters) {
                $q->where('loans.status', $filters['loan_list_filter_status']);
            })
            ->when(! empty($filters['only_repossessed'] ?? null),
                fn ($q) => $q->whereNotNull('loans.repossessed_at'),
                fn ($q) => $q->whereNull('loans.repossessed_at')
            )
            ->when(! empty($filters['only_execution'] ?? null),
                fn ($q) => $q->whereNotNull('loans.in_execution_at'),
                fn ($q) => $q->whereNull('loans.in_execution_at')
            )
            ->select(
                'loans.id',
                'loans.type',
                'loans.balance_to_financed',
                'loans.total_cost_loan',
                'loans.created_at',
                'loans.transaction_id',
                'loans.status',
                'loans.vin',
                'loans.customer_name',
                'loans.product_name',
                'loans.number_month',
                'loans.waiter',
                'loans.refinanced_at',
                'loans.interest_saved',
                'transactions.final_total as final_total',
                // Solo descuentos "estructurales" (pago a capital/refinanciamiento); los ligados
                // a una cuota puntual ya quedan neutralizados en delay - total_only_payments.
                DB::raw('(SELECT COALESCE(SUM(pa.amount_discounted),0)
                        FROM payment_applications AS pa
                        WHERE pa.loan_id = loans.id AND pa.payment_schedule_id IS NULL
                    ) as discount_amount'),
                DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount))
                        FROM transaction_payments AS TP
                        WHERE TP.transaction_id = transactions.id) as total_paid'),

                DB::raw('(SELECT
                            SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount))
                            FROM transaction_payments AS TP
                            WHERE TP.transaction_id = transactions.id AND TP.payment_schedule_id IS NOT NULL
                        ) as total_only_payments'),

                DB::raw('COALESCE(psa.delay,0) as delay'),
                DB::raw('COALESCE(da.mora,0) as mora'),
                DB::raw('COALESCE(psa.for_due,0) as for_due'),
                DB::raw('psa.next_due_date as next_due_date'),
                DB::raw('psa.loan_end_date as loan_end_date'),
                DB::raw('psa.next_due_amount as next_due_amount'),
            );
    }

    /**
     * Same money formulas used by the loans DataTable columns, without
     * the HTML wrapping, for consumers that need raw numeric values
     * (e.g. the dashboard API).
     */
    public function computeLoanTotals($row)
    {
        $mora = round($row->mora);

        $interest_saved = bcsub($row->discount_amount ?? 0, $row->interest_saved ?? 0, 2);
        if ($mora) {
            $total_delay = bcsub($row->delay, $row->total_only_payments, 4);
            if (! $row->refinanced_at) {
                $total_delay = bcsub($total_delay, $interest_saved, 4);
            }
        } else {
            $total_delay = 0;
        }
        if ($total_delay < 0 && $total_delay > -0.5) {
            $total_delay = 0;
        }

        if ($row->refinanced_at) {
            $total_to_delay = $row->for_due;
        } else {
            $paid_partial = $mora ? 0 : bcsub($row->delay, $row->total_only_payments, 4);
            $total_to_delay = $row->for_due + bcsub($paid_partial, $interest_saved, 4);
        }
        if ($total_to_delay < 0 && $total_to_delay > -0.5) {
            $total_to_delay = 0;
        }

        $total_remaining = $mora ? $total_delay + $row->mora : 0;
        if ($total_remaining < 0 && $total_remaining > -0.5) {
            $total_remaining = 0;
        }

        $total_mora = $row->mora;
        if ($total_mora < 0 && $total_mora > -0.5) {
            $total_mora = 0;
        }

        $total_remaining_mora = $row->final_total - $row->total_paid;
        if ($total_remaining_mora < 0 && $total_remaining_mora > -0.5) {
            $total_remaining_mora = 0;
        }

        return compact('total_delay', 'total_to_delay', 'total_remaining', 'total_mora', 'total_remaining_mora');
    }
}
