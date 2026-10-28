<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Utils\LoanUtil;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    protected $loanUtil;

    public function __construct(LoanUtil $loanUtil)
    {
        $this->loanUtil = $loanUtil;
    }

    /**
     * Same data as the /loans DataTable, as plain JSON for external dashboards.
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->can('unit.view'), 403);

        $business_id = $request->user()->business_id;

        $loans = $this->loanUtil
            ->loanListQuery($business_id, $request->all())
            ->paginate($request->input('per_page', 50));

        $loans->getCollection()->transform(function ($row) {
            $totals = $this->loanUtil->computeLoanTotals($row);

            return [
                'id' => $row->id,
                'created_at' => $row->created_at,
                'customer_name' => $row->customer_name,
                'product_name' => $row->product_name,
                'vin' => $row->vin,
                'final_total' => (float) $row->final_total,
                'total_paid' => (float) $row->total_paid,
                'total_delay' => (float) $totals['total_delay'],
                'total_mora' => (float) $totals['total_mora'],
                'total_remaining' => (float) $totals['total_remaining'],
                'total_to_delay' => (float) $totals['total_to_delay'],
                'total_remaining_mora' => (float) $totals['total_remaining_mora'],
                'waiter' => $row->waiter,
                'number_month' => $row->number_month,
                'balance_to_financed' => (float) $row->balance_to_financed,
                'next_due_date' => $row->next_due_date,
                'next_due_amount' => $row->next_due_amount !== null ? (float) $row->next_due_amount : null,
                'loan_end_date' => $row->loan_end_date,
                'status' => $row->status,
            ];
        });

        return response()->json($loans);
    }
}
