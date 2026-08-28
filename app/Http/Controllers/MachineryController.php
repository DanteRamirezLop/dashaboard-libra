<?php

namespace App\Http\Controllers;

use App\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MachineryController extends Controller
{
    /**
     * Feeds public/dashboard/machinery.html — seguimiento de cotizaciones vs
     * ventas de maquinaria a través del módulo de préstamos/financiamiento
     * (tabla `loans`), que ya queda acotado a la categoría "Maquinarias"
     * desde que se crea cada registro (ver LoanQuotationController y
     * LoanController). Año fijo en 2026 para que coincida con el
     * seguimiento anual pedido.
     * Secured by the api.token middleware (Bearer DASHBOARD_API_TOKEN), not user auth,
     * since the dashboard is a static file with no login.
     */
    public function index(Request $request)
    {
        $businessId = config('services.dashboard.business_id');

        $year = 2026;
        $from = $year.'-01-01';
        $to = $year.'-12-31';

        $rows = Loan::where('business_id', $businessId)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get([
                'id', 'status', 'date', 'customer_name', 'waiter', 'product_name',
                'type_quotation', 'product_price', 'quantity', 'contact_source', 'transaction_id',
            ]);

        $cotizaciones = $rows->where('status', 'quotation')
            ->values()
            ->map(fn ($row) => $this->mapRow($row));

        $ventasRows = $rows->where('status', '!=', 'quotation')->values();

        // Recaudado real (pagos ya cobrados) por venta, vía la transacción de venta
        // vinculada al préstamo. Todas las ventas quedan a crédito hoy, así que
        // "monto" (valor pactado) y "recaudado" (efectivo ya cobrado) difieren
        // bastante — de ahí la necesidad de mostrarlos por separado.
        $transactionIds = $ventasRows->pluck('transaction_id')->filter()->values();
        $recaudadoPorTransaccion = DB::table('transaction_payments')
            ->whereIn('transaction_id', $transactionIds)
            ->selectRaw('transaction_id, SUM(IF(is_return = 1, -1 * amount, amount)) as total')
            ->groupBy('transaction_id')
            ->pluck('total', 'transaction_id');

        $ventas = $ventasRows->map(fn ($row) => $this->mapRow($row) + [
            'estado' => $this->estadoLabel($row->status),
            'recaudado' => (float) ($recaudadoPorTransaccion[$row->transaction_id] ?? 0),
        ]);

        return response()->json([
            'cotizaciones' => $cotizaciones,
            'ventas' => $ventas,
            'period' => [
                'year' => $year,
                'from' => $from,
                'to' => $to,
            ],
            'generated_at' => Carbon::now()->toIso8601String(),
        ]);
    }

    protected function mapRow($row)
    {
        return [
            'id' => $row->id,
            'fecha' => $row->date,
            'cliente' => $row->customer_name ?: 'Sin nombre',
            'vendedor' => trim((string) $row->waiter) ?: 'Sin asignar',
            'producto' => $row->product_name ?: 'Sin especificar',
            'pago' => (int) $row->type_quotation === 2 ? 'Crédito' : 'Contado',
            'cantidad' => (int) ($row->quantity ?: 1),
            'monto' => (float) $row->product_price,
            'fuente' => $this->fuenteLabel($row->contact_source),
        ];
    }

    /**
     * Normaliza mayúsculas/minúsculas de contact_source: en producción conviven
     * variantes como "Whatsapp" y "WhatsApp" para el mismo canal (MySQL las trata
     * como iguales por su collation, pero el dashboard las compara en JS de forma
     * exacta) — sin esto, la mayoría de registros de WhatsApp quedaban agrupados
     * como "otras fuentes" en vez de "leads".
     */
    protected function fuenteLabel($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return 'Sin especificar';
        }

        $canonicas = [
            'contacto directo del vendedor' => 'Contacto directo del vendedor',
            'whatsapp' => 'WhatsApp',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'tiktok' => 'TikTok',
            'web de libra international' => 'Web de Libra International',
        ];

        return $canonicas[mb_strtolower($raw)] ?? $raw;
    }

    protected function estadoLabel($status)
    {
        return [
            'approved' => 'Aprobado',
            'partial' => 'Parcial',
            'in arrears' => 'Atrasado',
            'paid' => 'Pagado',
            'cancelled' => 'Cancelado',
            'repossessed' => 'Recuperado',
            'in execution' => 'Ejecución',
            'refinanced' => 'Refinanciado',
        ][$status] ?? ucfirst($status);
    }
}
