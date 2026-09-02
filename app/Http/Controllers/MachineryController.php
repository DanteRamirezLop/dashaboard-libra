<?php

namespace App\Http\Controllers;

use App\Category;
use App\Loan;
use App\Transaction;
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
     * LoanController). Trae todo desde 2024 hasta fin del año en curso —
     * el filtro por año (2024/2025/2026/todos) se aplica en el propio
     * dashboard (JS), no aquí, para no tener que volver a pegarle a la API
     * cada vez que el usuario cambia el año.
     * Además de `loans`, "ventas" incluye las ventas de maquinaria pagadas
     * por una financiera externa (ver ventasCreditoTerceros) y las ventas al
     * contado directo (ver ventasContado) — ninguna de las dos pasa por el
     * módulo de préstamos, así que no quedan registradas en `loans`.
     * También incluye "compras" (ver compras()): registros de compra con
     * custom_field_1 = 'Maquinarias', leídos directo de `transactions`.
     * Secured by the api.token middleware (Bearer DASHBOARD_API_TOKEN), not user auth,
     * since the dashboard is a static file with no login.
     */
    public function index(Request $request)
    {
        $businessId = config('services.dashboard.business_id');

        $from = '2024-01-01';
        $to = Carbon::now()->endOfYear()->toDateString();

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

        $ventasCreditoTerceros = $this->ventasCreditoTerceros($businessId, $from, $to, $transactionIds);
        $ventasContado = $this->ventasContado($businessId, $from, $to, $transactionIds->concat($ventasCreditoTerceros->pluck('id')));

        return response()->json([
            'cotizaciones' => $cotizaciones,
            'ventas' => $ventas->concat($ventasCreditoTerceros)->concat($ventasContado)->values(),
            'compras' => $this->compras($businessId, $from, $to),
            'period' => [
                'from' => $from,
                'to' => $to,
            ],
            'generated_at' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Compras de maquinaria: registros de `transactions` con `type` = 'purchase'
     * y `custom_field_1` = 'Maquinarias' (ver $purchase_type_options en
     * resources/views/purchase/create.blade.php y edit.blade.php — es el único
     * otro valor posible es 'Otros'). A diferencia de las ventas, las compras
     * no pasan por el módulo de préstamos, así que se leen directo de
     * `transactions`.
     */
    protected function compras($businessId, $from, $to)
    {
        $rows = Transaction::where('business_id', $businessId)
            ->where('type', 'purchase')
            ->whereBetween('transaction_date', [$from.' 00:00:00', $to.' 23:59:59'])
            ->where('custom_field_1', 'Maquinarias')
            ->with(['contact', 'purchase_lines.product'])
            ->orderBy('transaction_date')
            ->get();

        $pagadoPorTransaccion = DB::table('transaction_payments')
            ->whereIn('transaction_id', $rows->pluck('id'))
            ->selectRaw('transaction_id, SUM(IF(is_return = 1, -1 * amount, amount)) as total')
            ->groupBy('transaction_id')
            ->pluck('total', 'transaction_id');

        return $rows->map(function ($row) use ($pagadoPorTransaccion) {
            $lines = $row->purchase_lines;
            $firstProduct = optional($lines->first())->product;
            $producto = $firstProduct ? $firstProduct->name : 'Sin especificar';
            if ($lines->count() > 1) {
                $producto .= ' (+'.($lines->count() - 1).' más)';
            }

            return [
                'id' => $row->id,
                'fecha' => $row->transaction_date,
                'refNo' => $row->ref_no,
                'proveedor' => optional($row->contact)->full_name ?: 'Sin nombre',
                'producto' => $producto,
                'cantidad' => (float) $lines->sum('quantity'),
                'monto' => (float) $row->final_total,
                'pagado' => (float) ($pagadoPorTransaccion[$row->id] ?? 0),
                'estado' => $this->estadoLabelCompra($row->status),
                'estadoPago' => $this->estadoLabelPago($row->payment_status),
            ];
        })->values();
    }

    /**
     * Ventas de maquinaria pagadas por una financiera externa (p.ej. "XCMG
     * Finance"): la venta queda registrada como sell normal en `transactions`
     * con `custom_field_3` = nombre de la financiera (ver credito_tercero_options
     * en resources/views/sell/create.blade.php y edit.blade.php). Para
     * nosotros es una venta al contado — la financiera paga el total por
     * adelantado — por lo que nunca genera fila en `loans`, a diferencia de
     * las ventas financiadas por Libra internamente. $loanTransactionIds se
     * excluye por seguridad para no duplicar si alguna vez coincidieran.
     */
    protected function ventasCreditoTerceros($businessId, $from, $to, $loanTransactionIds)
    {
        $categoryId = Category::where('name', 'Maquinarias')->value('id');
        if (! $categoryId) {
            return collect();
        }

        $rows = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereBetween('transaction_date', [$from.' 00:00:00', $to.' 23:59:59'])
            ->whereNotNull('custom_field_3')
            ->where('custom_field_3', '!=', '')
            ->whereNotIn('id', $loanTransactionIds)
            ->whereHas('sell_lines.product', fn ($q) => $q->where('category_id', $categoryId))
            ->with(['contact', 'service_staff', 'sell_lines.product'])
            ->orderBy('transaction_date')
            ->get();

        $recaudadoPorTransaccion = DB::table('transaction_payments')
            ->whereIn('transaction_id', $rows->pluck('id'))
            ->selectRaw('transaction_id, SUM(IF(is_return = 1, -1 * amount, amount)) as total')
            ->groupBy('transaction_id')
            ->pluck('total', 'transaction_id');

        return $rows->map(function ($row) use ($recaudadoPorTransaccion) {
            $lines = $row->sell_lines;
            $firstProduct = optional($lines->first())->product;
            $producto = $firstProduct ? $firstProduct->name : 'Sin especificar';
            if ($lines->count() > 1) {
                $producto .= ' (+'.($lines->count() - 1).' más)';
            }

            return [
                'id' => $row->id,
                'fecha' => $row->transaction_date,
                'cliente' => optional($row->contact)->full_name ?: 'Sin nombre',
                'vendedor' => trim((string) optional($row->service_staff)->user_full_name) ?: 'Sin asignar',
                'producto' => $producto,
                'pago' => 'Crédito 3ros',
                'cantidad' => (float) $lines->sum('quantity'),
                'monto' => (float) $row->final_total,
                'fuente' => $row->custom_field_3,
                'estado' => $this->estadoLabelPago($row->payment_status),
                'recaudado' => (float) ($recaudadoPorTransaccion[$row->id] ?? 0),
            ];
        });
    }

    /**
     * Ventas de maquinaria pagadas al contado directo (sin financiera de por
     * medio): igual que ventasCreditoTerceros() pero filtrando por
     * `custom_field_4` = 'Contado' (ver $credito_contado_options en
     * resources/views/sell/create.blade.php y edit.blade.php) en vez de
     * `custom_field_3`. $excludeTransactionIds evita duplicar filas que ya
     * se contaron como venta financiada (loans) o crédito 3ros.
     */
    protected function ventasContado($businessId, $from, $to, $excludeTransactionIds)
    {
        $categoryId = Category::where('name', 'Maquinarias')->value('id');
        if (! $categoryId) {
            return collect();
        }

        $rows = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereBetween('transaction_date', [$from.' 00:00:00', $to.' 23:59:59'])
            ->where('custom_field_4', 'Contado')
            ->whereNotIn('id', $excludeTransactionIds)
            ->whereHas('sell_lines.product', fn ($q) => $q->where('category_id', $categoryId))
            ->with(['contact', 'service_staff', 'sell_lines.product'])
            ->orderBy('transaction_date')
            ->get();

        $recaudadoPorTransaccion = DB::table('transaction_payments')
            ->whereIn('transaction_id', $rows->pluck('id'))
            ->selectRaw('transaction_id, SUM(IF(is_return = 1, -1 * amount, amount)) as total')
            ->groupBy('transaction_id')
            ->pluck('total', 'transaction_id');

        return $rows->map(function ($row) use ($recaudadoPorTransaccion) {
            $lines = $row->sell_lines;
            $firstProduct = optional($lines->first())->product;
            $producto = $firstProduct ? $firstProduct->name : 'Sin especificar';
            if ($lines->count() > 1) {
                $producto .= ' (+'.($lines->count() - 1).' más)';
            }

            return [
                'id' => $row->id,
                'fecha' => $row->transaction_date,
                'cliente' => optional($row->contact)->full_name ?: 'Sin nombre',
                'vendedor' => trim((string) optional($row->service_staff)->user_full_name) ?: 'Sin asignar',
                'producto' => $producto,
                'pago' => 'Contado',
                'cantidad' => (float) $lines->sum('quantity'),
                'monto' => (float) $row->final_total,
                'fuente' => 'Sin especificar',
                'estado' => $this->estadoLabelPago($row->payment_status),
                'recaudado' => (float) ($recaudadoPorTransaccion[$row->id] ?? 0),
            ];
        });
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

    /**
     * Igual que estadoLabel() pero para transactions.status en compras
     * ('ordered'/'received'/'pending'), que usa un vocabulario distinto al
     * de loans.status.
     */
    protected function estadoLabelCompra($status)
    {
        return [
            'received' => 'Recibido',
            'pending' => 'Pendiente',
            'ordered' => 'Pedido',
        ][$status] ?? ucfirst((string) $status);
    }

    /**
     * Igual que estadoLabel() pero para transactions.payment_status (ventas
     * crédito 3ros), que usa un vocabulario distinto al de loans.status.
     */
    protected function estadoLabelPago($status)
    {
        return [
            'paid' => 'Pagado',
            'due' => 'Pendiente',
            'partial' => 'Parcial',
            'overdue' => 'Atrasado',
        ][$status] ?? ucfirst((string) $status);
    }
}
