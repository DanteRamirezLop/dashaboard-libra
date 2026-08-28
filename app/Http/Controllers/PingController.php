<?php

namespace App\Http\Controllers;

use App\Utils\LoanUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PingController extends Controller
{
    protected $loanUtil;

    public function __construct(LoanUtil $loanUtil)
    {
        $this->loanUtil = $loanUtil;
    }

    /**
     * Feeds public/dashaboard/dashboard.html.
     * Secured by the api.token middleware (Bearer DASHBOARD_API_TOKEN), not user auth,
     * since the dashboard is a static file with no login.
     */
    public function index(Request $request)
    {
        $businessId = config('services.dashboard.business_id');

        $days = (int) $request->input('days', 30);
        $days = max(1, min($days, 365));
        $to = Carbon::today()->endOfDay();
        $from = Carbon::today()->subDays($days - 1)->startOfDay();

        $loans = $this->loanUtil
            ->loanListQuery($businessId, $request->all())
            ->get()
            ->map(function ($row) {
                $totals = $this->loanUtil->computeLoanTotals($row);

                $venta = (float) $row->final_total;
                $pagado = (float) $row->total_paid;
                $vencido = (float) $totals['total_delay'];
                $mora = (float) $totals['total_mora'];
                $vm = (float) $totals['total_remaining'];
                $saldo = (float) $totals['total_remaining_mora'];
                $porVencer = max($saldo - $vm, 0);

                $cuota = $row->next_due_amount !== null
                    ? (float) $row->next_due_amount
                    : ($row->number_month > 0 ? (float) $row->total_cost_loan / $row->number_month : 0);

                $dias = $cuota > 0 ? (int) round($vencido / $cuota * 30) : 0;

                return [
                    'vend' => $row->waiter ?: 'Sin asignar',
                    'cliente' => $row->customer_name,
                    'maq' => $row->product_name,
                    'estado' => $this->estadoLabel($row->status),
                    'venta' => $venta,
                    'pagado' => $pagado,
                    'vencido' => $vencido,
                    'mora' => $mora,
                    'vm' => $vm,
                    'porVencer' => $porVencer,
                    'saldo' => $saldo,
                    'cuotas' => (int) $row->number_month,
                    'cuota' => $cuota,
                    'dias' => $dias,
                    'bucket' => $this->bucketLabel($dias),
                    'pctPag' => $venta > 0 ? round($pagado / $venta * 100, 1) : 0,
                    'pctVenc' => $saldo > 0 ? round($vm / $saldo * 100, 1) : 0,
                    // Prioridad 1 (DATOS_~1.MD): cronograma real en vez de estimaciones.
                    'vin' => $row->vin,
                    'creacion' => $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d') : null,
                    'fecha_vencimiento_cuota' => $row->next_due_date ? Carbon::parse($row->next_due_date)->format('Y-m-d') : null,
                    'monto_mes' => $cuota,
                    'fecha_fin' => $row->loan_end_date ? Carbon::parse($row->loan_end_date)->format('Y-m-d') : null,
                ];
            })
            ->values();

        $pagos = DB::table('transaction_payments as tp')
            ->join('transactions as t', 't.id', '=', 'tp.transaction_id')
            ->join('loans as l', 'l.transaction_id', '=', 't.id')
            ->where('l.business_id', $businessId)
            ->where('tp.is_return', 0)
            ->whereNotNull('tp.paid_on')
            ->whereBetween('tp.paid_on', [$from, $to])
            ->select('tp.paid_on', 'tp.amount', 'tp.method', 'l.customer_name')
            ->orderBy('tp.paid_on')
            ->get()
            ->map(fn ($row) => [
                'fecha' => Carbon::parse($row->paid_on)->format('Y-m-d'),
                'monto' => (float) $row->amount,
                'cliente' => $row->customer_name,
                'metodo' => $this->metodoLabel($row->method),
            ]);

        return response()->json([
            'loans' => $loans,
            'pagos' => $pagos,
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'days' => $days,
            ],
            'generated_at' => Carbon::now()->toIso8601String(),
        ]);
    }

    protected function estadoLabel($status)
    {
        return [
            'in arrears' => 'Atrasado',
            'partial' => 'Parcial',
            'paid' => 'Pagado',
            'repossessed' => 'Recuperado',
            'in execution' => 'Ejecución',
        ][$status] ?? ucfirst($status);
    }

    protected function bucketLabel(int $dias)
    {
        if ($dias <= 0) {
            return 'AL DÍA';
        }
        if ($dias <= 30) {
            return '1-30 DÍAS';
        }
        if ($dias <= 90) {
            return '31-90 DÍAS';
        }

        return '+90 DÍAS';
    }

    protected function metodoLabel($method)
    {
        return [
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'cheque' => 'Cheque',
            'bank_transfer' => 'Transferencia bancaria',
            'other' => 'Otro',
        ][$method] ?? ucfirst($method);
    }
}
