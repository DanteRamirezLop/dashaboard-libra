<?php

namespace App\Http\Controllers;

use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LeadsController extends Controller
{
    protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }

    /**
     * Feeds public/dashboard/leads.html — avance de metas de venta de maquinaria
     * (cotizaciones y cierres, propios vs. leads) por vendedor, comparado contra
     * las metas 2026 (cargadas aparte desde SEGUIMIENTO_DE_METAS_2026.xlsx).
     * Secured by the api.token middleware (Bearer DASHBOARD_API_TOKEN), not user auth,
     * since the dashboard is a static file with no login.
     */
    public function index(Request $request)
    {
        $businessId = config('services.dashboard.business_id');

        // El dashboard y las METAS cargadas solo cubren el plan 2026: se fija el año
        // acá (se ignora cualquier ?year= de la query) para que nunca se sirvan datos
        // de otro año contra un plan que no existe.
        $year = 2026;
        $from = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $to = Carbon::createFromDate($year, 12, 31)->endOfDay();

        $cierres = $this->transactionUtil
            ->getListSells($businessId)
            ->whereBetween('transactions.transaction_date', [$from, $to])
            ->groupBy('transactions.id')
            ->orderBy('transactions.transaction_date')
            ->get()
            ->map(function ($row) {
                $total = (float) $row->final_total;
                $pagado = (float) $row->total_paid;

                // El vendedor real es quien atiende al cliente ("Personal de servicio" /
                // res_waiter_id), no quien tipea la venta en el sistema — quien registra
                // la venta suele ser personal de oficina, no el vendedor. Por eso ya no
                // se usa added_by como respaldo: si la venta no marcó Personal de
                // servicio, el vendedor queda sin identificar.
                $waiter = trim(preg_replace('/\s+/', ' ', (string) $row->waiter));

                return [
                    'id' => $row->id,
                    'factura' => $row->invoice_no,
                    'fecha' => Carbon::parse($row->transaction_date)->format('Y-m-d'),
                    'cliente' => $row->name ?: ($row->supplier_business_name ?: 'Sin nombre'),
                    'vendedor' => $waiter !== '' ? $waiter : 'Sin asignar',
                    'fuente_contacto' => $row->custom_field_2 ?: 'Sin especificar',
                    'unidades' => (int) $row->total_items,
                    'total' => $total,
                    'pagado' => $pagado,
                    'pendiente' => max($total - $pagado, 0),
                ];
            })
            ->values();

        // Cotizaciones actualmente abiertas (is_quotation=1). Ojo: al confirmarse una
        // cotización como venta, el ERP le borra el flag is_quotation y la fecha de
        // creación original — por eso esto es una foto del pipeline vigente, no un
        // conteo histórico de "cotizaciones emitidas ese mes" (ver nota en el dashboard).
        $cotizaciones = DB::table('transactions as t')
            ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->leftJoin('users as w', 't.res_waiter_id', '=', 'w.id')
            ->where('t.business_id', $businessId)
            ->where('t.type', 'sell')
            ->where('t.is_quotation', 1)
            ->whereBetween('t.created_at', [$from, $to])
            ->select(
                't.id',
                't.invoice_no as factura',
                't.created_at',
                'c.name as cliente',
                'c.supplier_business_name',
                't.custom_field_2 as fuente_contacto',
                't.final_total as total',
                DB::raw("CONCAT(COALESCE(w.surname, ''),' ',COALESCE(w.first_name, ''),' ',COALESCE(w.last_name,'')) as waiter")
            )
            ->orderBy('t.created_at')
            ->get()
            ->map(function ($row) {
                // Mismo criterio que en "cierres": el vendedor es el Personal de
                // servicio que atiende al cliente, no quien tipea la cotización.
                $waiter = trim(preg_replace('/\s+/', ' ', (string) $row->waiter));

                return [
                    'id' => $row->id,
                    'factura' => $row->factura,
                    'fecha' => Carbon::parse($row->created_at)->format('Y-m-d'),
                    'cliente' => $row->cliente ?: ($row->supplier_business_name ?: 'Sin nombre'),
                    'vendedor' => $waiter !== '' ? $waiter : 'Sin asignar',
                    'fuente_contacto' => $row->fuente_contacto ?: 'Sin especificar',
                    'total' => (float) $row->total,
                ];
            })
            ->values();

        return response()->json([
            'cierres' => $cierres,
            'cotizaciones' => $cotizaciones,
            'period' => [
                'year' => $year,
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'generated_at' => Carbon::now()->toIso8601String(),
        ]);
    }
}
