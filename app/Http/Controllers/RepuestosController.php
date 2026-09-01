<?php

namespace App\Http\Controllers;

use App\Category;
use App\Loan;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RepuestosController extends Controller
{
    /**
     * Feeds the "vendedor de repuestos" section of public/dashboard/ventas-vs-cotizaciones.html.
     * Unlike MachineryController (which reads the `loans` table, scoped to the
     * Maquinarias category), this reads plain sell transactions business-wide,
     * with no product category filter. What separates a "repuestos" sale from
     * a "maquinaria" one here isn't the category or who created it, but
     * whether a `loans` row references that transaction (loans.transaction_id):
     * that link is what technically marks a sale as machinery financing, so
     * any transaction without it counts as a repuestos-section sale. The one
     * exception is a machinery sale paid by an external financiera (Maquinarias
     * category + custom_field_3 set, see MachineryController::ventasCreditoTerceros) —
     * it never gets a `loans` row either, but MachineryController already
     * counts it, so it's excluded here too to avoid double-counting.
     * "vendedor": attributed by "Personal de servicio" (res_waiter_id), not by
     * who typed the sale (created_by) — office staff frequently register a
     * sale on the salesperson's behalf, so created_by would undercount them
     * (see LeadsController for the same reasoning). Users holding the
     * "vendedor de repuestos" role (e.g. Rosa Dioses) get their own card when
     * marked as Personal de servicio; every other transaction — mostly
     * counter sales with no dedicated field salesperson assigned — is
     * grouped under "Oficina", mirroring how the `loans` table already
     * labels unassigned-salesperson machinery quotes as waiter="Oficina".
     * Cotización = draft sell with is_quotation=1 (the POS quotation feature,
     * not a loan quotation); venta = status final. Año fijo 2026, igual que
     * el resto del dashboard.
     * Secured by the api.token middleware (Bearer DASHBOARD_API_TOKEN), not user auth,
     * since the dashboard is a static file with no login.
     */
    public function index(Request $request)
    {
        $businessId = config('services.dashboard.business_id');

        $year = 2026;
        $from = $year.'-01-01 00:00:00';
        $to = $year.'-12-31 23:59:59';

        $roleIds = Role::where('business_id', $businessId)
            ->where('name', 'like', 'vendedor de repuestos%')
            ->pluck('id');

        $roleUserIds = DB::table('model_has_roles')
            ->whereIn('role_id', $roleIds)
            ->where('model_type', User::class)
            ->pluck('model_id');

        $rows = Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->whereBetween('transaction_date', [$from, $to])
            ->where(function ($q) {
                $q->where('status', 'final')
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'draft')->where('is_quotation', 1);
                    });
            })
            ->with(['contact', 'service_staff', 'sell_lines.product'])
            ->orderBy('transaction_date')
            ->get();

        $loanTransactionIds = Loan::where('business_id', $businessId)
            ->whereIn('transaction_id', $rows->pluck('id'))
            ->pluck('transaction_id')
            ->flip();

        $categoryId = Category::where('name', 'Maquinarias')->value('id');
        $credito3rosIds = $categoryId
            ? Transaction::where('business_id', $businessId)
                ->whereIn('id', $rows->pluck('id'))
                ->whereNotNull('custom_field_3')
                ->where('custom_field_3', '!=', '')
                ->whereHas('sell_lines.product', fn ($q) => $q->where('category_id', $categoryId))
                ->pluck('id')
                ->flip()
            : collect();

        $rows = $rows->reject(fn ($row) => isset($loanTransactionIds[$row->id]) || isset($credito3rosIds[$row->id]))->values();

        $ventaRows = $rows->where('status', 'final')->values();

        $recaudadoPorTransaccion = DB::table('transaction_payments')
            ->whereIn('transaction_id', $ventaRows->pluck('id'))
            ->selectRaw('transaction_id, SUM(IF(is_return = 1, -1 * amount, amount)) as total')
            ->groupBy('transaction_id')
            ->pluck('total', 'transaction_id');

        $cotizaciones = $rows->where('status', 'draft')
            ->values()
            ->map(fn ($row) => $this->mapRow($row, $roleUserIds));

        $ventas = $ventaRows->map(fn ($row) => $this->mapRow($row, $roleUserIds) + [
            'estado' => $this->estadoLabel($row->payment_status),
            'recaudado' => (float) ($recaudadoPorTransaccion[$row->id] ?? 0),
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

    protected function mapRow($row, $roleUserIds)
    {
        $lines = $row->sell_lines;
        $firstProduct = optional($lines->first())->product;
        $producto = $firstProduct ? $firstProduct->name : 'Sin especificar';
        if ($lines->count() > 1) {
            $producto .= ' (+'.($lines->count() - 1).' más)';
        }

        $vendedor = $roleUserIds->contains($row->res_waiter_id)
            ? (trim((string) optional($row->service_staff)->user_full_name) ?: 'Sin asignar')
            : 'Oficina';

        return [
            'id' => $row->id,
            'fecha' => $row->transaction_date,
            'cliente' => optional($row->contact)->full_name ?: 'Sin nombre',
            'vendedor' => $vendedor,
            'producto' => $producto,
            'cantidad' => (float) $lines->sum('quantity'),
            'monto' => (float) $row->final_total,
        ];
    }

    protected function estadoLabel($status)
    {
        return [
            'paid' => 'Pagado',
            'due' => 'Pendiente',
            'partial' => 'Parcial',
            'overdue' => 'Atrasado',
        ][$status] ?? ucfirst((string) $status);
    }
}
