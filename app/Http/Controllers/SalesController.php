<?php

namespace App\Http\Controllers;

use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SalesController extends Controller
{
    protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
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

        $sales = $this->transactionUtil
            ->getListSells($businessId)
            ->whereBetween('transactions.transaction_date', [$from, $to])
            ->groupBy('transactions.id')
            ->orderBy('transactions.transaction_date', 'desc')
            ->get()
            ->map(function ($row) {
                $total = (float) $row->final_total;
                $pagado = (float) $row->total_paid;

                return [
                    'id' => $row->id,
                    'factura' => $row->invoice_no,
                    'fecha' => $row->sale_date,
                    'cliente' => $row->name,
                    'vendedor' => trim(preg_replace('/\s+/', ' ', $row->added_by)) ?: 'Sin asignar',
                    'sucursal' => $row->business_location,
                    'estado_pago' => $this->estadoPagoLabel($row->payment_status),
                    'total' => $total,
                    'pagado' => $pagado,
                    'pendiente' => max($total - $pagado, 0),
                ];
            })
            ->values();

        return response()->json([
            'ventas' => $sales,
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'days' => $days,
            ],
            'generated_at' => Carbon::now()->toIso8601String(),
        ]);
    }

    protected function estadoPagoLabel($status)
    {
        return [
            'due' => 'Pendiente',
            'partial' => 'Parcial',
            'paid' => 'Pagado',
        ][$status] ?? ucfirst($status);
    }
}
