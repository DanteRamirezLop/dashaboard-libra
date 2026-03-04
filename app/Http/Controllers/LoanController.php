<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\ModuleUtil;
use Yajra\DataTables\Facades\DataTables;
use App\Contact;
use App\Loan;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use Carbon\Carbon;
use App\Product;
use App\User;
use App\Category;
use App\Utils\ContactUtil;
use App\Variation;
use App\LoanSetting;
use App\Delay;
use App\PaymentSchedule;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\DB;
use App\BusinessLocation;
use App\Transaction;
use App\TransactionSellLine;
use App\TransactionSellLinesPurchaseLines;
use App\TransactionPayment;
use App\InvoiceScheme;
use App\ScheduleVersion;
use App\PaymentApplication;
use App\ExchangeRates;

class LoanController extends Controller {
    /**
     * All Utils instance.
     */
    protected $transactionUtil;
    protected $moduleUtil;
    protected $productUtil;
    protected $businessUtil;
    protected $contactUtil;
    /**
     * Create a new controller instance.
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil,ModuleUtil $moduleUtil, BusinessUtil $businessUtil, ProductUtil $productUtil, ContactUtil $contactUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;
        $this->businessUtil = $businessUtil;
        $this->contactUtil = $contactUtil;
        $this->transactionUtil = $transactionUtil;
    }

    public function index(){   
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
           $psAgg = DB::table('payment_schedules as ps')
            ->leftJoin('schedule_versions as sv', 'sv.id', '=', 'ps.schedule_version_id')
            ->selectRaw("
                ps.loan_id,
                COALESCE(SUM(
                    CASE WHEN ps.status <> 'pending'
                    THEN ps.mount_quota + ps.gps_quota + ps.sure_quota + ps.admin_fee_quota + ps.initial
                    ELSE 0 END
                ),0) as delay,

                COALESCE(SUM(
                    CASE WHEN ps.status = 'pending'
                    THEN ps.mount_quota + ps.gps_quota + ps.sure_quota + ps.admin_fee_quota + ps.initial
                    ELSE 0 END
                ),0) as for_due
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

            $loans = Loan::query()
            ->leftJoin('transactions', 'loans.transaction_id', '=', 'transactions.id')
            ->leftJoinSub($psAgg, 'psa', function ($join) {
                $join->on('psa.loan_id', '=', 'loans.id');
            })
            ->leftJoinSub($dAgg, 'da', function ($join) {
                $join->on('da.loan_id', '=', 'loans.id');
            })
            ->where('loans.business_id', $business_id)
            ->where('loans.status', '!=', 'quotation')
            ->select(
                'loans.id',
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
                'transactions.final_total as final_total',
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
            )->get();
        
            return Datatables::of($loans)->addColumn(
                    'action',
                     function ($row){
                             if (auth()->user()->can('user.view') || auth()->user()->can('user.create') || auth()->user()->can('roles.view')){                
                                $html = '<div class="btn-group">
                                    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info tw-w-max dropdown-toggle"
                                        data-toggle="dropdown" aria-expanded="false">'.
                                        __('messages.actions').
                                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                        <ul class="dropdown-menu dropdown-menu-left" role="menu">.   
                                            <li><a href="'.route('add-letter-loan',[$row->id]).'"><i class="fa fa-list" aria-hidden="true"></i> Asignar número de letra</a></li>';

                                $html .= '<li class="divider"></li>';
                                $html .= '<li><a href="'.action([\App\Http\Controllers\LoanController::class, 'show'], [$row->id]).'" "><i class="fas fa fa-calendar" aria-hidden="true"></i> Calendario de pagos</a></li>';
                                $html .= '<li class="divider"></li>';
                                $html .= '<li><a href="'.action([\App\Http\Controllers\LoanPaymentController::class, 'statemenPDF'], [$row->id]).'"  ><i class="fas fa fa-download" aria-hidden="true"></i> Descargar estado de cuenta</a></li>';
                                $html .= '<li><a href="#" class="print-invoice" data-href="'.route('sell.printInvoice', [$row->transaction_id]).'"><i class="fas fa-print" aria-hidden="true"></i> '.__('lang_v1.print_invoice').'</a></li>';
                                $html .= '<li><a href="'.action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->transaction_id]).'" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> '.__('purchase.view_payments').'</a></li>';
                                $html .= '<li><a href="#" data-href="'.action([\App\Http\Controllers\SellController::class, 'show'], [$row->transaction_id]).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> '.__('messages.view').'</a></li>';
                                $html .= '<li><a href="'.action([\App\Http\Controllers\LoanController::class, 'destroy'], [$row->id]).'" class="delete-sale"> <i class="fas fa-trash"></i> '.__('messages.delete').'</a></li>';
                        }else{
                          $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                        data-toggle="dropdown" aria-expanded="false">'.
                                        __('messages.actions').
                                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                     <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                     <li><a href="'.action([\App\Http\Controllers\LoanPaymentController::class, 'statemenPDF'], [$row->id]).'"  ><i class="fas fa fa-download" aria-hidden="true"></i> Descargar estado de cuenta</a></li>';

                            $html .= '<li class="divider"></li>';
                            $html .= '<li><a href="'.action([\App\Http\Controllers\LoanController::class, 'show'], [$row->id]).'" "><i class="fas fa fa-calendar" aria-hidden="true"></i> Calendario de pagos</a></li>';
                        } 
                        $html .= '</ul></div>';
                        return $html;
                     }
                )->addColumn(
                    'label',
                    function ($row){
                        switch ($row->status) {
                            case "approved":
                                $label = '<span class="label label-info">Aprobado</span>';
                                break;
                            case"partial":
                                $label = '<span class="label label-info">Parcial</span>';
                                break;
                            case"in arrears":
                                $label = '<span class="label label-danger">Atrasado</span>';
                                break;
                            case"cancelled":
                                $label = '<span class="label label-default">Cancelado</span>';
                                break;
                            case"paid":
                                $label = '<span class="label label-success">pagado</span>';
                                break;
                        }
                        return $label;
                     }
                )
                 ->addColumn('total_delay', function ($row) {
                     
                   $mora = round($row->mora);
                    if($mora){
                        $total_delay = bcsub($row->delay, $row->total_only_payments, 4);
                    }else{
                        $total_delay = 0;
                    }

                     if($total_delay < 0 && $total_delay > -0.25) {
                        $total_delay = 0;
                    }
                    $total_delay_html = '<span class="payment_due" data-orig-value="'.$total_delay.'">'.$this->transactionUtil->num_f($total_delay, true).'</span>';
                    return $total_delay_html;
                })

                ->addColumn('total_to_delay', function ($row) {
                    $mora = round($row->mora);
                    if($mora){
                        $paid_partial = 0;
                    }else{
                        $paid_partial = bcsub($row->delay, $row->total_only_payments, 4);
                    }
                    $total_to_delay =  $row->for_due + $paid_partial;
                    $total_to_delay_html = '<span class="payment_due" data-orig-value="'.$total_to_delay.'">'.$this->transactionUtil->num_f($total_to_delay, true).'</span>';
                    return $total_to_delay_html;
                })
                ->addColumn('total_remaining', function ($row) {
                     $mora = round($row->mora);
                    if($mora){
                         $total_remaining = bcsub($row->delay, $row->total_only_payments, 4) + $row->mora;
                    }else{
                        $total_remaining = 0;
                    }
                    
                    if($total_remaining < 0 && $total_remaining > -0.25) {
                        $total_remaining = 0;
                    }
                    
                    $total_remaining_html = '<span class="payment_due" data-orig-value="'.$total_remaining.'">'.$this->transactionUtil->num_f($total_remaining, true).'</span>';
                    return $total_remaining_html;
                })

                 ->addColumn('total_mora', function ($row) {
                    $total_mora = $row->mora;
                    $total_mora_html = '<span class="payment_due" data-orig-value="'.$total_mora.'">'.$this->transactionUtil->num_f($total_mora, true).'</span>';
                    return $total_mora_html;
                })

                 ->addColumn('total_remaining_mora', function ($row) {
                    $total_remaining = $row->final_total - $row->total_paid;
                    $total_remaining_html = '<span class="payment_due" data-orig-value="'.$total_remaining.'">'.$this->transactionUtil->num_f($total_remaining, true).'</span>';
                    return $total_remaining_html;
                })

                ->editColumn(
                    'final_total',
                    '<span class="final-total" data-orig-value="{{$final_total}}"> @format_currency($final_total)  </span>'
                )
                ->editColumn(
                    'total_paid',
                    '<span class="total-paid" data-orig-value="{{$total_paid}}">@format_currency($total_paid)</span>'
                )

                ->editColumn('balance_to_financed','@format_currency(number_format($balance_to_financed))')
                ->editColumn('total_cost_loan','@format_currency(number_format($total_cost_loan))')
                ->editColumn('created_at','{{date("Y/m/d",strtotime($created_at))}}')
                ->rawColumns(['action','label','seller','total','final_total','total_paid','total_remaining','total_delay','total_to_delay','total_mora','total_remaining_mora'])
                ->make(true);
        }
        $type = request()->get('id');
        return view('loan.index',compact('type'));
    }


     public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $customer = Contact::where('type', 'customer')->where('business_id',$business_id)->get();
        $category = Category::where("name","Maquinarias")->first();
        $products = Product::where('category_id',$category->id)->where('is_inactive',0)->get();
        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        //variables en Credito
        $filing_fee = LoanSetting::where('name','coste-tramite')->first();
        $gps = LoanSetting::where('name','gps')->first();
        $insurance = LoanSetting::where('name','seguro')->first();
        $waiters = $this->transactionUtil->getModuleStaff($business_id,'customer.view_own',true);
        $rightNow = Carbon::now();
        $currency = $this->transactionUtil->currentCurrency($business_id);
        return view('loan.create',compact('currency','customer','products','walk_in_customer','filing_fee','gps','insurance','waiters','rightNow'));
    }


    public function store(Request $request)
    {
        try {
            $output = DB::transaction(function () use ($request) {

                // -------------------- Inputs --------------------
                $business_id = $request->session()->get('user.business_id');
                $user_id     = auth()->id();

                $initial_amount           = (float) $request->input('pay_initial');
                $annual_interest_rate     = (float) $request->input('multiplayer'); // antes: multiplier
                $number_month             = (int) $request->input('number_month');
                $type_initial             = (int) $request->input('type_initial'); // 1 completo, 2 fraccionado
                $meses_gps_seguro         = (int) $request->input('mounth_expenses_financed');
                $mounth_fracction_initial = (int) $request->input('mounth_fracction');

                $option_tramite = (int) $request->input('option_tramite');
                $option_gps     = (int) $request->input('option_gps');
                $option_seguro  = (int) $request->input('option_seguro');

                // -------------------- Validaciones de meses --------------------
                if ($number_month < $meses_gps_seguro || $number_month < $mounth_fracction_initial) {
                    return [
                        'redirect' => redirect('loans')->with('status', [
                            'success' => false,
                            'msg' => 'EL PRESTAMO NO SE CREO: los meses del financiamiento no pueden ser mayor a los meses del préstamo.'
                        ])
                    ];
                }
                // -------------------- Fecha --------------------
                $created_on = $this->transactionUtil->uf_date($request->input('created_on'), true);
                $startDate  = Carbon::parse($created_on);
                // -------------------- Cliente --------------------
                $customer_id = (int) $request->input('contact_id');
                $customer = Contact::findOrFail($customer_id);

                $mobile = $request->input('mobile')
                    ? $this->formatearTelefonoPeru($request->input('mobile'))
                    : null;

                $customer->email  = $request->input('email');
                $customer->mobile = $mobile;
                $customer->save();

                // -------------------- Producto / Variación --------------------
                $product_id   = (int) $request->input('product_id');
                $variation_id = (int) $request->input('variation');
                $product   = Product::findOrFail($product_id);
                $variation = Variation::findOrFail($variation_id);
                $product_mount = (float) $variation->sell_price_inc_tax;
                // -------------------- Datos extra --------------------
                $customer_name = $request->input('customer'); // antes lo guardabas como type_product
                $waiter        = $request->input('waiter');
                // -------------------- Goals en 1 query --------------------
                $loan_setting = LoanSetting::whereIn('name', [
                    'terminos-y-condiciones',
                    'coste-tramite',
                    'gps',
                    'seguro'
                ])->get()->keyBy('name');

                $terms = optional($loan_setting->get('terminos-y-condiciones'))->description;
                // -------------------- Cálculo de gastos (trámite / gps / seguro) --------------------
                // Iniciales (se cobran con inicial)
                $initial_admin_fee = 0.0;
                $initial_gps       = 0.0;
                $initial_insurance = 0.0;
                // Saldos a financiar (se agregan al préstamo en cuotas)
                $admin_fee_quotes    = 0.0;
                $gps_quotes          = 0.0;
                $insurance_quotes    = 0.0;
                // Cuotas mensuales de esos saldos financiados
                $admin_fee_cuote = 0.0;
                $gps_cuote       = 0.0;
                $insurance_cuote = 0.0;
                $months_expenses = max($meses_gps_seguro, 1); // evita división 0

                if ($option_tramite === 1) {
                    $tbl = $loan_setting->get('coste-tramite');
                    $initial_admin_fee = (float) optional($tbl)->amount_inicial;
                    $admin_fee_total   = (float) optional($tbl)->amount_total;
                    $admin_fee_quotes  = round($admin_fee_total - $initial_admin_fee, 2);
                    $admin_fee_cuote   = $admin_fee_quotes / $months_expenses;
                }

                if ($option_gps === 1) {
                    $tbl = $loan_setting->get('gps');
                    $initial_gps     = (float) optional($tbl)->amount_inicial;
                    $gps_total       = (float) optional($tbl)->amount_total;
                    $gps_quotes      = round($gps_total - $initial_gps, 2);
                    $gps_cuote       = $gps_quotes / $months_expenses;
                }

                if ($option_seguro === 1) {
                    $tbl = $loan_setting->get('seguro');
                    $initial_insurance = (float) optional($tbl)->amount_inicial;
                    $insurance_total   = (float) optional($tbl)->amount_total;
                    $insurance_quotes  = round($insurance_total - $initial_insurance, 2);
                    $insurance_cuote   = $insurance_quotes / $months_expenses;
                }

                // -------------------- Porcentaje inicial --------------------
                $pay_initial_percentage = $product_mount > 0
                    ? (100 * $initial_amount) / $product_mount
                    : 0;

                // -------------------- Fracción de inicial (si aplica) --------------------
                $amount_fracction  = 0.0;
                $mounth_fracction  = 0;
                $initial_cuotes    = 0.0;
                $taxes_fraccion    = 0.0;

                if ($type_initial === 2) {
                    $amount_fracction = (float) $request->input('amount_fracction');
                    $mounth_fracction = (int) $request->input('mounth_fracction');
                    $rate_fracction   = (float) $request->input('rate_fracction');
                    $initial_cuotes = (float) $this->calculateQuote($rate_fracction, $mounth_fracction, $amount_fracction);
                    $mount_axu      = $initial_cuotes * $mounth_fracction;
                    $taxes_fraccion = (float) bcsub((string) $mount_axu, (string) $amount_fracction, 4);
                }

                // -------------------- Monto a financiar --------------------
                $balance_to_financed = round($product_mount - $initial_amount, 4); // antes: loan_amount

                // -------------------- Cuota francesa --------------------
                $tasaMensual = ($annual_interest_rate / 100) / 12;

                if ($number_month <= 0) {
                    throw new \Exception("number_month inválido");
                }

                if ($tasaMensual > 0) {
                    $cuota = $balance_to_financed
                        * ($tasaMensual * pow(1 + $tasaMensual, $number_month))
                        / (pow(1 + $tasaMensual, $number_month) - 1);
                } else {
                    $cuota = $balance_to_financed / $number_month;
                }
                $amount_fraccion = round($cuota, 4);
                // -------------------- Cronograma --------------------
                $saldo = $balance_to_financed;
                $quotes = [];

                $dateCursor = $startDate->copy(); // importante: no mutar el startDate original

                for ($i = 1; $i <= $number_month; $i++) {
                    $dateCursor = $dateCursor->copy()->addMonth(); // evita acumulación rara por mutación accidental

                    $saldo_inicial = $saldo;
                    $interes       = $saldo * $tasaMensual;
                    $amortizacion  = $cuota - $interes;

                    $saldo -= $amortizacion;
                    if ($saldo < 0) $saldo = 0;

                    $initial_fraccion_pay = ($i <= $mounth_fracction) ? $initial_cuotes : 0;

                    $gps_pay     = ($i <= $meses_gps_seguro) ? $gps_cuote : 0;
                    $seguro_pay  = ($i <= $meses_gps_seguro) ? $insurance_cuote : 0;
                    $admin_pay   = ($i <= $meses_gps_seguro) ? $admin_fee_cuote : 0;

                    $quotes[] = [
                        'id'            => $i,
                        'date'          => $dateCursor->format('Y-m-d'),
                        'saldo_inicial' => $saldo_inicial,
                        'amount'        => $amount_fraccion,
                        'capital'       => $amortizacion,
                        'interes'       => $interes,
                        'saldo_final'   => $saldo,
                        'total_pay'     => 0,
                        'status'        => 0,
                        'gps'           => $gps_pay,
                        'seguro'        => $seguro_pay,
                        'initial'       => $initial_fraccion_pay,
                        'admin_fee'     => $admin_pay,
                    ];
                }

                // -------------------- Totales --------------------
                $coste_prestamo = $amount_fraccion * $number_month;

                // Total del préstamo incluyendo saldos financiados de gps/seguro/tramite
                $total_cost_loan = $coste_prestamo + $gps_quotes + $insurance_quotes + $admin_fee_quotes; // antes: amount

                // Interés total del préstamo (sin contar gps/seguro/tramite)
                $total_amount_interest = round($coste_prestamo - $balance_to_financed, 4); // antes: rate

                // -------------------- Anexos --------------------
                $annexes = json_encode([
                    "anexo_1" => $request->input('anexo_1'),
                    "anexo_2" => $request->input('anexo_2'),
                    "anexo_3" => $request->input('anexo_3'),
                    "anexo_4" => $request->input('anexo_4'),
                ]);

                // -------------------- Crear Loan (COLUMNAS NUEVAS) --------------------
                $loan = Loan::create([
                    'customer_id' => $customer_id,
                    'user_id'     => $user_id,
                    'business_id' => $business_id,
                    'product_id'  => $product_id,

                    'status'      => 'approved',
                    'product_name'=> $product->name,
                    'date'        => $created_on,

                    // CAMBIOS DE COLUMNAS:
                    'customer_name'         => $customer_name,         // antes: type_product
                    'type_quotation'        => 2,                      // antes: period (1 contado / 2 crédito)
                    'number_month'          => $number_month,
                    'annual_interest_rate'  => $annual_interest_rate,  // antes: multiplier
                    'total_amount_interest' => $total_amount_interest, // antes: rate
                    'total_cost_loan'       => $total_cost_loan,       // antes: amount
                    'balance_to_financed'   => $balance_to_financed,   // antes: loan_amount

                    'quotes' => json_encode($quotes),

                    'initial_admin_fee' => $initial_admin_fee,  // antes: admin_fee
                    'initial_gps'       => $initial_gps,        // antes: gps
                    'initial_insurance' => $initial_insurance,  // antes: insurance

                    // Mantengo tus columnas existentes:
                    'gps_quotes'        => $gps_quotes,
                    'insurance_quotes'  => $insurance_quotes,
                    'admin_fee_quotes'  => $admin_fee_quotes,

                    'product_price'       => $product_mount,
                    'initial_percentage'  => $pay_initial_percentage,
                    'initial_amount'      => $initial_amount,

                    'contact_source' => $request->input('contact_source'),
                    'terms'          => $terms,
                    'waiter'         => $waiter,
                    'vin'            => $request->input('vin'),
                    'annexes'        => $annexes,

                    'initial_fraction' => $amount_fracction,
                    'mounth_initial'   => $mounth_fracction,
                    'start_rate'       => $taxes_fraccion,
                ]);

                // -------------------- Resto de tu flujo (igual) --------------------
                $this->generateQuota($loan);

                $input = $this->newSale($loan, $taxes_fraccion);

                $invoice_total = [
                    'total_before_tax' => $product_mount,
                    'tax' => 0,
                ];

                $transaction = $this->transactionUtil->createSellTransaction(
                    $business_id, $input, $invoice_total, $user_id, false
                );

                $transaction->payment_status = 'partial';
                $transaction->save();

                $loan->transaction_id = $transaction->id;
                $loan->status = 'partial';
                $loan->save();

                $paid_on = Carbon::parse($created_on);

                $this->newSaleLine($loan, $transaction->id, $variation_id);

                $note_init = 'Initial ';
                $initial_to_pay = $initial_amount;

                if ($initial_admin_fee != 0) {
                    $initial_to_pay += $initial_admin_fee;
                    $note_init .= '+ Coste de tramite ';
                }
                if ($initial_gps != 0) {
                    $initial_to_pay += $initial_gps;
                    $note_init .= '+ Inicial GPS ';
                }
                if ($initial_insurance != 0) {
                    $initial_to_pay += $initial_insurance;
                    $note_init .= '+ Inicial del seguro ';
                }

                $initial_to_pay = round($initial_to_pay - $amount_fracction, 4);

                $this->newTransaction($transaction, $initial_to_pay, $user_id, $customer_id, $paid_on, $note_init);

                return ['success' => true, 'msg' => 'successfully added'];
            });

            // Si en validación retornamos un redirect “empaquetado”
            if (isset($output['redirect'])) {
                return $output['redirect'];
            }

        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());
            $output = ['success' => false, 'msg' => 'Error: '.$e->getLine().' Message: '.$e->getMessage()];
        }

        return redirect('loans')->with('status', $output);
    }

   private function newSale(Loan $loan, float $taxes_fraccion): array
    {
        $location_id = BusinessLocation::where('business_id', $loan->business_id)->value('id');

        // Iniciales (nuevas columnas)
        $initial_admin_fee = (float) ($loan->initial_admin_fee ?? 0);
        $initial_gps       = (float) ($loan->initial_gps ?? 0);
        $initial_insurance = (float) ($loan->initial_insurance ?? 0);

        // Fraccionados/financiados (si existen en tu tabla)
        $admin_fee_quotes  = (float) ($loan->admin_fee_quotes ?? 0);
        $gps_quotes        = (float) ($loan->gps_quotes ?? 0);
        $insurance_quotes  = (float) ($loan->insurance_quotes ?? 0);

        // “Opcionales” = iniciales + fraccionados (tú decides si incluye o no iniciales)
        $mount_optional = $initial_admin_fee + $initial_gps + $initial_insurance
                        + $admin_fee_quotes + $gps_quotes + $insurance_quotes;

        // Intereses (nuevo)
        $interest_total = (float) ($loan->total_amount_interest ?? 0);

        // Total final: producto + intereses + opcionales + intereses fracción inicial
        $final_total = (float) $loan->product_price + $interest_total + $mount_optional + $taxes_fraccion;

        return [
            'location_id' => $location_id,
            'contact_id' => $loan->customer_id,
            'res_waiter_id' => auth()->id(),
            'final_total' => $final_total,
            'status' => 'final',
            'additional_notes' => '',
            'transaction_date' => now(),
            'tax_rate_id' => null,
            'sale_note' => null,
            'commission_agent' => null,
            'discount_type' => 'percentage',
            'discount_amount' => 0,
            'is_direct_sale' => 1,
            'exchange_rate' => 1,
            'recur_interval' => 1,
            'recur_interval_type' => 'days',
            'pay_term_number' => $loan->number_month,
            'pay_term_type' => 'months',

            'additional_expense_key_1' => 'Importe total de los intereses',
            'additional_expense_value_1' => $interest_total,

            'additional_expense_key_2' => 'Cargos / Intereses por mora',
            'additional_expense_value_2' => 0,

            'additional_expense_key_3' => 'Coste de tramite, GPS y seguro',
            'additional_expense_value_3' => $mount_optional,

            'additional_expense_key_4' => 'Importe de los intereses de inicial',
            'additional_expense_value_4' => $taxes_fraccion,
        ];
    }

    private function newTransaction(Transaction $transaction,float $amount,int $user_id,int $payment_for,Carbon $paid_on,string $note_init): TransactionPayment 
    {
        $ref_count = $this->transactionUtil->setAndGetReferenceCount('sell_payment', $transaction->business_id);
        $payment_ref_no = $this->transactionUtil->generateReferenceNumber('sell_payment', $ref_count, $transaction->business_id);

        return TransactionPayment::create([
            'transaction_id' => $transaction->id,
            'business_id' => $transaction->business_id,
            'is_return' => 0,
            'amount' => $amount,
            'method' => 'cash',
            'paid_on' => $paid_on,
            'created_by' => $user_id,
            'paid_through_link' => 0,
            'is_advance' => 0,
            'payment_for' => $payment_for,
            'note' => $note_init,
            'payment_ref_no' => $payment_ref_no,

            // si tu tabla lo permite, puedes omitirlos; si no, déjalos:
            'card_type' => 'credit',
            'account_id' => null,
            'parent_id' => null,
            'gateway' => null,
            'payment_type' => null,
            'transaction_no' => null,
            'document' => null,
            'cheque_number' => null,
            'bank_account_number' => null,
            'card_transaction_number' => null,
            'card_number' => null,
            'card_holder_name' => null,
            'card_month' => null,
            'card_year' => null,
            'card_security' => null,
        ]);
    }

    private function newSaleLine(Loan $loan, int $transaction_id, int $variation_id): void
    {
        $sellLine = TransactionSellLine::create([
            'transaction_id' => $transaction_id,
            'product_id' => $loan->product_id,
            'variation_id' => $variation_id,
            'quantity' => 1,
            'quantity_returned' => 0,

            'unit_price_before_discount' => $loan->product_price,
            'unit_price' => $loan->product_price,
            'unit_price_inc_tax' => $loan->product_price,

            'line_discount_type' => 'fixed',
            'line_discount_amount' => 0,
            'item_tax' => 0,
            'tax_id' => null,

            // defaults / opcionales
            'mfg_waste_percent' => 0,
            'secondary_unit_quantity' => 0,
            'discount_id' => null,
            'sell_line_note' => null,
            'so_quantity_invoiced' => 0,
            'children_type' => '',
            'sub_unit_id' => null,
            'res_service_staff_id' => null,
            'res_line_order_status' => null,
            'so_line_id' => null,
        ]);

        TransactionSellLinesPurchaseLines::create([
            'sell_line_id' => $sellLine->id,
            'stock_adjustment_line_id' => null,
            'purchase_line_id' => 0,
            'quantity' => 1,
            'qty_returned' => 0,
        ]);
    }
    

    private function calculateQuote(float $annual_rate, int $months, float $principal): float
    {
        if ($months <= 0) {
            return 0.0;
        }

        $monthly_rate = ($annual_rate / 100) / 12;

        if ($monthly_rate <= 0) {
            return round($principal / $months, 4);
        }

        $factor = pow(1 + $monthly_rate, $months);
        $payment = $principal * ($monthly_rate * $factor) / ($factor - 1);

        return round($payment, 4);
    }
   
    private function generateQuota(Loan $loan): void
    {
        $quotas = json_decode($loan->quotes, false) ?? [];

        if (empty($quotas)) {
            return;
        }

        $scheme = InvoiceScheme::where('business_id', $loan->business_id)
            ->where('name', 'Numero de letra')
            ->first();

        $count  = $scheme?->invoice_count ?? 1;
        $prefix = $scheme?->prefix ?? '000-';

        $rows = [];
        $now = now();

        foreach ($quotas as $quota) {
            $number_letter = $prefix . $count;

            $rows[] = [
                'loan_id' => $loan->id,
                'number_quota' => $quota->id,
                'sheduled_date' => $quota->date,
                'mount_quota' => $quota->amount,
                'status' => 'pending',
                'opening_balance' => $quota->saldo_inicial,
                'capital' => $quota->capital,
                'interests' => $quota->interes,
                'final_balance' => $quota->saldo_final,
                'gps_quota' => $quota->gps,
                'sure_quota' => $quota->seguro,
                'admin_fee_quota' => $quota->admin_fee,
                'number_letter' => $number_letter,
                'initial' => $quota->initial ?? 0,

                // si tu tabla lo tiene:
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $count++;
        }

        // Inserta todo de una (MUCHO más rápido)
        PaymentSchedule::insert($rows);

        if ($scheme) {
            $scheme->invoice_count = $count;
            $scheme->save();
        }
    }

    private function formatearTelefonoPeru($numero) {
        // Eliminar espacios, guiones y otros caracteres no numéricos excepto el "+"
        $numero = preg_replace('/[^\d+]/', '', $numero);
        // Si el número ya comienza con +51, lo devolvemos tal cual
        if (strpos($numero, '+51') === 0) {
            return $numero;
        }
        // Si comienza con 51 sin el "+", le agregamos el "+"
        if (strpos($numero, '51') === 0) {
            return '+' . $numero;
        }
        // Si no tiene el código, se lo agregamos
        return '+51' . $numero;
    }

    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $type = request()->get('type');
        $loan = Loan::find($id);
        $annexes = json_decode($loan->annexes);
        $countVersion = ScheduleVersion::where('loan_id', $loan->id)->count();
        $scheduleVersionId = ScheduleVersion::where('loan_id', $loan->id)
        ->where('status', 'active')
        ->value('id'); // trae solo el id (o null)

        $paymentSchedules = PaymentSchedule::where('loan_id', $loan->id)
            ->when($scheduleVersionId, fn ($q) => $q->where('schedule_version_id', $scheduleVersionId))
            ->get();

        $customer = Contact::find($loan->customer_id);
		$user = User::find($loan->user_id);
        $total =  $loan->amount + $loan->admin_fee + $loan->gps  + $loan->insurance  +  $loan->initial_amount + $loan->start_rate;
        // ver si no tiene cuotas parciales para habilitar el pago a capital                       
        $canPayCapital = !PaymentSchedule::where('loan_id',$loan->id)->whereNotIn('status',['paid','pending'])->exists();
        //hay mora?
        $there_is_mora =  Delay::where('loan_id',$loan->id)->where('status','late')->exists(); //Mora actual 
        return view('loan.show')->with(compact('countVersion','annexes','canPayCapital','there_is_mora','paymentSchedules','type','customer','loan','user','total'));
    }

    public function update(Request $request, $id){
        try {
            $loan = Loan::find($id);
            $date_right_now = Carbon::now();
            $countVersion = ScheduleVersion::where('loan_id', $loan->id)->count();
            if($countVersion){
              $output = ['success' => False,'msg' => 'No se puedo actulizar',];
            }else{
                foreach($loan->paymentSchedule as $item){
                    $date_shedule = Carbon::parse($item->sheduled_date);
                    $days_late = $date_shedule->diffInDays($date_right_now, false); //Dias atrasado
                    if($days_late > 0){
                        if($item->status != 'paid'){
                            $item->status ='overdue';
                            $item->save();
                        }
                    }
                }
                $output = ['success' => true,'msg' => 'Actualizado',];
            }
            
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = ['success' => false,'msg' => 'Error: '.$e->getLine().'Message:'.$e->getMessage(),];
        }
        return $output;
    }

    public function destroy($id)
    {
        if (! auth()->user()->can('loan.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;
                $loan = Loan::where('business_id', $business_id)->findOrFail($id);
                
                if ($loan) {
                    DB::beginTransaction();
                    $transaction_id = $loan->transaction_id;
                    if ($transaction_id) {
                        $output = $this->transactionUtil->deleteSale($business_id, $transaction_id); //Eliminar la transacción
                        $loan->delete(); //Eliminar el Prestamo y sus relaciones
                        $output = ['success' => true,'msg' => __('loan.deleted_success'),];
                    }else{
                         $output = ['success' => false,'msg' =>  __('lang_v1.loan_cannot_be_deleted'),];
                    }
                } else {
                    $output = ['success' => false,'msg' =>  __('lang_v1.loan_cannot_be_deleted'),];
                }

                DB::commit();
            } catch (\Exception $e) {
                 DB::rollBack();
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
                $output = ['success' => false,
                    'msg' => '__("messages.something_went_wrong")',
                ];
            }
            return $output;
        }
    }

    public function addCapital($loan_id,$type)
    {
        if (! auth()->user()->can('purchase.payments') && ! auth()->user()->can('sell.payments') && ! auth()->user()->can('all_expense.access') && ! auth()->user()->can('view_own_expense')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (request()->ajax()) {
                $loan = Loan::find($loan_id);
                $schedule_version = ScheduleVersion::where('loan_id', $loan->id)->where('status','active')->first();
                $schedule_version_id = $schedule_version ? $schedule_version->id : NULL;
                $business_id = request()->session()->get('user.business_id');
                $transaction = Transaction::where('business_id', $business_id)->with(['contact', 'location'])->findOrFail( $loan->transaction_id);
                if ($transaction->payment_status != 'paid') {
                    $show_advance = in_array($transaction->type, ['sell', 'purchase']) ? true : false;
                    $payment_types = $this->transactionUtil->payment_types(null, false,$business_id);
                    $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);
                    $paid_on = Carbon::now()->toDateTimeString();
                    $rows = PaymentSchedule::query()
                    ->where('loan_id', $loan->id)
                    ->where('schedule_version_id', $schedule_version_id)
                    ->orderBy('id')
                    ->get();

                    $nextPending = $rows->firstWhere('status', 'pending');
                    if (!$nextPending) {
                        return; // no hay cuotas pendientes
                    }

                    $amount = (float) $nextPending->opening_balance;

                    if($type == 'total'){
                        $other_expense = $this->transactionUtil->getOtherExpensesPaid($loan->id); // Monto de otros gastos pagados como el gps, seguro que en si son tiene intereses solo estan fraccionados 
                        $amount = round($amount + $other_expense,4); 
                        $amount_formated = $this->transactionUtil->num_f($amount);
                        $view = view('loan.payment_total')->with(compact('transaction','loan','amount','amount_formated','paid_on','payment_types','accounts','type'))->render();
                    }else{
                        $amount_formated = $this->transactionUtil->num_f($amount);
                        $view = view('loan.payment_capital')->with(compact('transaction','loan','amount','amount_formated','paid_on','payment_types','accounts','type'))->render();
                    }

                    $output = ['status' => 'due','view' => $view];
                } else {
                    $output = ['status' => 'paid','view' => '','msg' => __('purchase.amount_already_paid'),];
                }
                return json_encode($output);
            }
            //code...
        } catch (\Throwable $th) {
           Log::emergency('File:'.$th->getFile().'Line:'.$th->getLine().'Message:'.$th->getMessage());
           $output = ['success' => false,
            'msg' => 'Error: '.$th->getLine().'Message:'.$th->getMessage(),
            ];  
        }
    }

    public function addPayment($payment_schedules_id){
        if (request()->ajax()) {
            //busco el tipo de cambio del dia
            $search_date = Carbon::now()->format('Y-m-d');
            $exchange_rate = ExchangeRates::whereDate('search_date', $search_date)->value('sale');
            if (!$exchange_rate || $exchange_rate <= 0) {
                $exchange_rate = 1;
            }
            $exchange_rate = round((float) $exchange_rate, 4);
            $payment_schedule = PaymentSchedule::findOrFail($payment_schedules_id);
            if ($payment_schedule->payment_status != 'paid') {
                $business_id = request()->session()->get('user.business_id');
                //Accounts
                $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);
                $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);
                $amount = $this->transactionUtil->amountToPay($payment_schedule);
                $paid_on = Carbon::now()->toDateTimeString();
                $view = view('loan.payment_row')->with(compact('exchange_rate','payment_schedule','amount','paid_on','payment_types','accounts'))->render();
                $output = ['status' => 'due','view' => $view, ];
            } else {
                $output = ['status' => 'paid','view' => '','msg' => __('purchase.amount_already_paid'),  ];
            }
            return json_encode($output);
        }
    }

    public function updateLetterAnnexe(Request $request){
        try {
            
            if($request->type == 'letter'){
                $item = PaymentSchedule::find($request->id);
                $item->number_letter = $request->value;
                $item->save();
            }

            if($request->type == 'annexe'){
                $loan =  Loan::find($request->id);
                $annexes = json_decode($loan->annexes);

                if ($request->celda == 'anexo_1') {
                    $annexes->anexo_1 = $request->value;
                }

                if ($request->celda == 'anexo_2') {
                    $annexes->anexo_2 = $request->value;
                }

                if ($request->celda == 'anexo_3') {
                    $annexes->anexo_3 = $request->value;
                }

                 if ($request->celda == 'anexo_4') {
                    $annexes->anexo_4 = $request->value;
                }

                $loan->annexes = json_encode($annexes);
                $loan->save();
            }

            $output = ['success' => true,'msg' => 'Actualizado'];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = ['success' => false,'msg' => 'Error: '.$e->getLine().'Message:'.$e->getMessage(),];
        }
        return $output;
    }

    public function addLetterLoan($id){
        $business_id = request()->session()->get('user.business_id');
        $type = request()->get('type');
        $loan = Loan::find($id);
        $annexes = json_decode($loan->annexes);
        $paymentSchedules = PaymentSchedule::where('loan_id', $loan->id)->get();
        $customer = Contact::find($loan->customer_id);
		$user = User::find($loan->user_id);
        $total =  $loan->amount + $loan->admin_fee + $loan->gps  + $loan->insurance  +  $loan->initial_amount;
        return view('loan.number_letter')->with(compact('annexes','paymentSchedules','type','customer','loan','user','total'));
    }

    public function prices(Request $request){
        $options = '';
        $options .= '<option selected disabled >Selecciona un precio</option>';
        $variations = Variation::where('product_id',$request->id)->get();
        foreach ($variations as $key => $variation) {
            $options .= "<option data-price='".$variation->sell_price_inc_tax."'  value='".$variation->id."'> ".  number_format($variation->sell_price_inc_tax,2) ." </option>";
        }
        return response()->json(['status' => true, 'options' =>  $options]);
    }

    public function getCustomerSunat(Request $request){
        $business_id = $request->session()->get('user.business_id');
        $created_by  = $request->session()->get('user.id');
        $type = $request->input('type'); // 'dni' o 'ruc'
        // 1) Validar tipo y leer valor
        if (!in_array($type, ['dni', 'ruc'])) {
            return response()->json(['status' => false, 'msg' => 'Tipo inválido']);
        }

        $value = $request->input($type); // dni o ruc
        if (empty($value)) {
            return response()->json(['status' => false, 'msg' => strtoupper($type) . ' requerido']);
        }
        // 2) Buscar contacto local
        $query = Contact::where('contact_id', $value)->where('business_id', $business_id);
        $contact = $query->first();
        // 3) Si existe, asegurar acceso y responder
        if ($contact) {
            $this->ensureContactAccess($contact, $query, $created_by);
            return response()->json([
                'status'     => true,
                'name'       => $type === 'dni' ? $contact->name : $contact->supplier_business_name,
                'contact_id' => $contact->id,
                'mobile'     => $contact->mobile,
                'email'      => $contact->email,
            ]);
        }

        // 4) Config por tipo (endpoint, payload)
        $config = [
            'dni' => [
                'url' => 'https://apiperu.dev/api/dni',
                'payload' => ['dni' => $value],
            ],
            'ruc' => [
                'url' => 'https://apiperu.dev/api/ruc',
                'payload' => ['ruc' => $value],
            ],
        ];

        // 5) Llamada API
        [$ok, $dataOrError] = $this->callApiPeru($config[$type]['url'], $config[$type]['payload']);

        if (!$ok) {
            return response()->json(['status' => false, 'msg' => 'ERROR']);
        }

        $data = $dataOrError;
        if (empty($data['success'])) {
            return response()->json(['status' => false, 'msg' => strtoupper($type) . ' no encontrado']);
        }

        // 6) Crear contacto según tipo
        $contact = $this->createContactFromApi($type, $data['data'], $business_id, $created_by);
        $contact->save();
        return response()->json([
            'status'     => true,
            'name'       => $type === 'dni' ? $contact->name : $contact->supplier_business_name,
            'contact_id' => $contact->id,
            'mobile'     => $contact->mobile,
            'email'      => $contact->email,
        ]);
    }
    /**
     * Mantiene tu misma lógica de permisos, pero reutilizable.
     */
    private function ensureContactAccess(Contact $contact, $baseQuery, int $created_by): void
    {
        $is_my_contact = (clone $baseQuery)->where('created_by', $created_by)->exists();

        if ($is_my_contact) {
            return;
        }

        $is_admin = $this->contactUtil->is_admin(auth()->user());
        if ($is_admin) {
            return;
        }

        $assigned_to_user = $contact->userHavingAccess()
            ->wherePivot('user_id', $created_by)
            ->exists();

        if (!$assigned_to_user) {
            $contact->userHavingAccess()->attach($created_by);
        }
    }
    /**
     * cURL centralizado.
     */
    private function callApiPeru(string $url, array $payload): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . config('services.apiperu.token'),
            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return [false, $err];
        }

        return [true, json_decode($response, true)];
    }
    /**
     * Mapper de data por tipo.
     */
    private function createContactFromApi(string $type, array $data, int $business_id, int $created_by): Contact
    {
        $common = [
            'business_id'     => $business_id,
            'type'            => 'customer',
            'contact_status'  => 'active',
            'mobile'          => '999999999',
            'email'           => 'ejemplo@gmail.com',
            'created_by'      => $created_by,
        ];

        if ($type === 'dni') {
            return new Contact($common + [
                'name'       => $data['nombre_completo'] ?? null,
                'first_name' => $data['nombres'] ?? null,
                'last_name'  => trim(($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? '')),
                'contact_id' => $data['numero'] ?? null,
            ]);
        }

        // ruc
        return new Contact($common + [
            'supplier_business_name' => $data['nombre_o_razon_social'] ?? null,
            'contact_id'             => $data['ruc'] ?? null,
            'city'                   => $data['provincia'] ?? null,
            'state'                  => $data['departamento'] ?? null,
            'address_line_1'         => $data['direccion_completa'] ?? null,
        ]);
    }
}
