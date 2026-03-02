<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\ModuleUtil;
use App\Contact;
use App\Loan;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use App\Mail\Loanquotes;
use App\Product;
use App\User;
use App\Category;
use App\Utils\ContactUtil;
use App\Variation;
use App\Media;
use App\LoanSetting;
use App\Charts\CommonChart;
use App\BusinessLocation;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;

class LoanQuotationController extends Controller
{

    /**
     * All Utils instance.
     */
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

    public function index()
    {
        $user = auth()->user();
        $isAdmin = $this->businessUtil->is_admin($user);

        if (request()->ajax()) {
            $businessId = request()->session()->get('user.business_id');
            // Si is_credit = 1 => type_quotation 2, si no => type_quotation 1
            $type_quotation = request()->boolean('is_credit') ? 2 : 1;

            $query = Loan::query()
                ->where('business_id', $businessId)
                ->where('status', 'quotation')
                ->where('type_quotation', $type_quotation)
                ->when(!$isAdmin, fn ($q) => $q->where('user_id', $user->id))
                ->orderByDesc('id');

           return Datatables::of($query)
                ->addColumn('action', '
                    <form action="/dowload-loan-quotation-pdf" method="post" style="display: contents;">
                        @csrf
                        <input type="hidden" name="id" value="{{$id}}">
                        <button type="submit" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-warning btn-xs">
                            <i class="fas fa-file-pdf"></i> @lang("loand.download_pdf")
                        </button>
                    </form>
                    <a href="{{action(\'App\Http\Controllers\LoanQuotationController@show\', [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info ">
                        <i class="fas fa-eye"></i> @lang("loand.see_details")
                    </a>
                    @can("loand_setting.access")
                        <button data-href="{{action(\'App\Http\Controllers\LoanQuotationController@destroy\', [$id])}}"
                            class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_loan_button">
                            <i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")
                        </button>
                    @endcan
                ')
        
                ->editColumn('seller', fn ($row) => $row->getNameUser())
                ->editColumn('annual_interest_rate', fn ($row) => $row->annual_interest_rate . '%')
                ->editColumn('created_at', fn ($row) => optional($row->created_at)->format('Y/m/d'))
                ->editColumn('type_quotation', fn ($row) => $row->product_name) // (si realmente existe ese atributo)
                ->editColumn('balance_to_financed', '@format_currency($balance_to_financed)')
                ->editColumn('product_price', '@format_currency($product_price)')
                ->editColumn('total_cost_loan', '@format_currency($total_cost_loan)')
                ->editColumn('total_amount_interest', '@format_currency($total_amount_interest)')
                ->rawColumns(['action','total_amount_interest','rate','total_cost_loan','product_price','balance_to_financed'])
                ->make(true);
        }

        $type = request()->get('id');
        return view('loan.quotation.index', compact('type'));
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $currency = $this->transactionUtil->currentCurrency($business_id);
        // Customers (solo lo que usualmente se necesita para selects)
        $customers = Contact::query()
            ->where('type', 'customer')
            ->where('business_id', $business_id)
            ->select(['id', 'name', 'supplier_business_name', 'mobile', 'email']) // ajusta a lo que uses
            ->orderBy('name')
            ->get();
        // Category "Maquinarias" (manejo de nulo)
        $categoryId = Category::query()->where('name', 'Maquinarias')->value('id'); // devuelve id o null
        $products = collect();
        if ($categoryId) {
                $products = Product::query()
                ->where('category_id',$categoryId)
                ->where('is_inactive',0)
                ->where(function ($q) {
                        $q->whereNull('product_custom_field6')
                            ->orWhere('product_custom_field6', 0);
                })->get();
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        // Traer todos los goals en 1 sola query
        $goals = LoanSetting::query()
            ->whereIn('name', ['coste-tramite', 'gps', 'seguro', 'initial'])
            ->get()
            ->keyBy('name');

        $filing_fee = (float) optional($goals->get('coste-tramite'))->amount_total ?? 0;
        $gps        = (float) optional($goals->get('gps'))->amount_total ?? 0;
        $insurance  = (float) optional($goals->get('seguro'))->amount_total ?? 0;
        $data = compact('currency','customers', 'products', 'walk_in_customer', 'filing_fee', 'gps', 'insurance');

        if (auth()->user()->can('loand_settings.access')) {
            $waiters = $this->transactionUtil->getModuleStaff($business_id, 'customer.view_own', true);
            $initial = $goals->get('initial');
            $percentages = $initial?->description ? json_decode($initial->description, true) : [];
            return view('loan.quotation.create_admin', $data + compact('percentages', 'waiters'));
        }

        return view('loan.quotation.create', $data);
    }


    public function store(Request $request)
    {
        try {
            // DATOS COMUNES
            $business_id = request()->session()->get('user.business_id'); 
            $type_quotation = $request->input('option'); //Credito o Contado
            $customer_id = $request->input('contact_id');
            $mobile = $request->input('mobile') ? $this->formatearTelefonoPeru($request->input('mobile')) : $request->input('mobile');//validar prefijo numerico Peru +51
            $product_id = $request->input('product_id');
            $product_name = Product::where('id', $product_id)->value('name'); 
            $date = Carbon::now();
            $option_tramite = $request->input('option_tramite');
            $option_gps = $request->input('option_gps');
            $option_seguro = $request->input('option_seguro');

            // 1) Actualizar contacto (1 query)
            Contact::whereKey($customer_id)->update([
                'email'  => $request->input('email'),
                'mobile' => $mobile,
            ]);

            $customer_name = $request->input('customer');

            // 2) Datos simples
            $user   = auth()->user();
            $waiter = $request->input('waiter') ?: trim($user->first_name . ' ' . $user->last_name);

            // 3) Precio del producto (evita cargar todo Variation)
            $product_mount = $request->filled('prices')
                ? (float) $request->input('prices')
                : (float) Variation::where('product_id', $product_id)->value('sell_price_inc_tax'); // 1 query

            // 4) Traer LoanSettings en un solo query (terminos, tramite, gps, seguro)
            $goals = LoanSetting::whereIn('name', ['terminos-y-condiciones', 'coste-tramite', 'gps', 'seguro'])
                ->get()
                ->keyBy('name');

            $terms = data_get($goals, 'terminos-y-condiciones.description', '');

            // Defaults (para contado o cuando no aplique)
            $json = [];
            $pay_initial = 0;
            $initial_amount = 0;
            $admin_fee = 0;

            $gps_init = 0;
            $gps_coutes = 0;
            $gps_quotes = 0;
            $gps_amount_total = 0;

            $seguro_init = 0;
            $seguro_coutes = 0;
            $insurance_quotes = 0;
            $seguro_amount_total = 0;

            $number_month = 0;
            $annual_interest_rate = 0;
            $intereses = 0;
            $coste_prestamo_gps_seguro = 0;
            $loan_amount = 0;

            // Asegurar Carbon
            $date = $date instanceof Carbon ? $date : Carbon::parse($date);

            // 5) Crédito
            if ((int) $type_quotation === 2) {
                $number_month = (int) $request->input('number_month');
                $meses_gps_seguro = min($number_month, 12);

                // Tramite
                if ((int) $option_tramite === 1) {
                    $admin_fee = (float) data_get($goals, 'coste-tramite.amount_total', 0);
                }

                // GPS
                if ((int) $option_gps === 1) {
                    $gps_init         = (float) data_get($goals, 'gps.amount_inicial', 0);
                    $gps_amount_total = (float) data_get($goals, 'gps.amount_total', 0);
                    $gps_quotes  = max(0, $gps_amount_total - $gps_init);
                    $gps_coutes  = $meses_gps_seguro > 0 ? ($gps_quotes / $meses_gps_seguro) : 0;
                }

                // Seguro
                if ((int) $option_seguro === 1) {
                    $seguro_init         = (float) data_get($goals, 'seguro.amount_inicial', 0);
                    $seguro_amount_total = (float) data_get($goals, 'seguro.amount_total', 0);
                    $insurance_quotes = max(0, $seguro_amount_total - $seguro_init);
                    $seguro_coutes    = $meses_gps_seguro > 0 ? ($insurance_quotes / $meses_gps_seguro) : 0;
                }

                $pay_initial     = (float) $request->input('pay_initial');   // %
                $annual_interest_rate     = (float) $request->input('annual_interest_rate');   // tasa anual %
                $initial_amount  = $product_mount * ($pay_initial / 100);
                $loan_amount     = max(0, $product_mount - $initial_amount);

                // Cuota (préstamo francés)
                $tasaMensual = ($annual_interest_rate / 100) / 12;

                if ($number_month <= 0) {
                    $cuota = 0;
                } elseif ($tasaMensual > 0) {
                    $factor = pow(1 + $tasaMensual, $number_month);
                    $cuota  = $loan_amount * ($tasaMensual * $factor) / ($factor - 1);
                } else {
                    $cuota = $loan_amount / $number_month;
                }

                $amount_fraccion = round($cuota, 4);

                // Cronograma
                $saldo = $loan_amount;

                for ($i = 1; $i <= $number_month; $i++) {
                    $date_quota = $date->copy()->addMonths($i)->format('Y-m-d');

                    $saldo_inicial = $saldo;
                    $interes_mes   = $saldo * $tasaMensual;
                    $amortizacion  = $cuota - $interes_mes;
                    $saldo = max(0, $saldo - $amortizacion);
                    $gps    = ($i <= $meses_gps_seguro) ? $gps_coutes : 0;
                    $seguro = ($i <= $meses_gps_seguro) ? $seguro_coutes : 0;

                    $json[] = [
                        'id'            => $i,
                        'date'          => $date_quota,
                        'saldo_inicial' => $saldo_inicial,
                        'amount'        => $amount_fraccion,
                        'capital'       => $amortizacion,
                        'interes'       => $interes_mes,
                        'saldo_final'   => $saldo,
                        'total_pay'     => 0,
                        'status'        => 0,
                        'gps'           => $gps,
                        'seguro'        => $seguro,
                    ];
                }

                $coste_prestamo = $amount_fraccion * $number_month;
                $coste_prestamo_gps_seguro = $coste_prestamo + $gps_quotes + $insurance_quotes;
                $intereses = $coste_prestamo - $loan_amount;
            }

            // 6) Crear loan
            Loan::create([
                'customer_id'         => $customer_id,
                'user_id'             => $user->id,
                'business_id'         => $business_id,
                'product_id'          => $product_id,
                'status'              => 'quotation',
                'product_name'        => $product_name,
                'date'                => Carbon::now(),
                'customer_name'       => $customer_name,
                'type_quotation'      => (int) $type_quotation,
                'number_month'        => $number_month,
                'annual_interest_rate'=> $annual_interest_rate,
                'total_amount_interest' => $intereses,
                'total_cost_loan'     => $coste_prestamo_gps_seguro,
                'quotes'              => json_encode($json),
                'initial_admin_fee'   => $admin_fee,
                'initial_gps'         => $gps_init,
                'initial_insurance'   => $seguro_init,
                'gps_quotes'          => $gps_quotes,
                'insurance_quotes'    => $insurance_quotes,
                'loan_amount'         => $loan_amount,
                'product_price'       => $product_mount,
                'initial_percentage'  => $pay_initial,
                'initial_amount'      => $initial_amount,
                'contact_source'      => $request->input('contact_source'),
                'terms'               => $terms,
                'waiter'              => $waiter,
            ]);

            $output = ['success' => true, 'msg' => __('loand.created_successfully')];

        } catch (\Throwable $e) {
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => 'Error al crear la cotización. Consulte con el administrador.'];
        }

        return redirect('loans-quotations')->with('status', $output);
    }


    public function storeAdmin(Request $request){
        try {
            $output = DB::transaction(function () use ($request) {

                $business_id = $request->session()->get('user.business_id');

                $product_id  = (int) $request->input('product_id');
                $customer_id = (int) $request->input('contact_id');

                $initial_amount   = (float) $request->input('pay_initial', 0);
                $type_initial     = (int) $request->input('type_initial', 1);
                $annualRate       = (float) $request->input('annual_interest_rate', 0); // Tasa anual (input antiguo)
                $typeQuotation    = (int) $request->input('option'); // 1 contado, 2 crédito (input antiguo)
                $option_tramite   = (int) $request->input('option_tramite', 0);
                $option_gps       = (int) $request->input('option_gps', 0);
                $option_seguro    = (int) $request->input('option_seguro', 0);

                // Teléfono
                $mobileRaw = $request->input('mobile');
                $mobile = $mobileRaw ? $this->formatearTelefonoPeru($mobileRaw) : null;

                // Actualizar contacto (1 query)
                Contact::whereKey($customer_id)->update([
                    'email'  => $request->input('email'),
                    'mobile' => $mobile,
                ]);

                // Producto
                $product = Product::select('id', 'name')->findOrFail($product_id);

                // Precio de maquinaria
                $product_mount = $request->filled('prices')
                    ? (float) $request->input('prices')
                    : (float) optional(
                        Variation::where('product_id', $product_id)->select('sell_price_inc_tax')->first()
                    )->sell_price_inc_tax;

                // Mozo / asesor
                $waiter = $request->input('waiter')
                    ?: (auth()->user()->first_name . ' ' . auth()->user()->last_name);

                // “customer” (en tu código actual es type_product)
                $customer_name = $request->input('customer');

                // Cargar LoanSettings en un solo query
                $goals = LoanSetting::whereIn('name', [
                    'terminos-y-condiciones',
                    'coste-tramite',
                    'gps',
                    'seguro',
                ])->get()->keyBy('name');

                $terms = (string) optional($goals->get('terminos-y-condiciones'))->description;

                // Defaults (contado)
                $number_month               = 0;
                $total_amount_interest      = 0; // antes: rate
                $total_cost_loan            = 0; // antes: amount
                $initial_admin_fee          = 0; // antes: admin_fee
                $initial_gps                = 0; // antes: gps
                $initial_insurance          = 0; // antes: insurance
                $balance_to_financed        = 0; // antes: loan_amount
                $gps_quotes                 = 0;
                $insurance_quotes           = 0;
                $initial_percentage         = 0;

                $amount_fracction           = 0;
                $mounth_fracction           = 0;
                $initial_cuotes             = 0;
                $taxes_fraccion             = 0;

                $json = [];

                // ======= COTIZACIÓN A CRÉDITO =======
                if ($typeQuotation === 2) {

                    $number_month = (int) $request->input('number_month', 0);
                    $meses_gps_seguro = ($number_month < 12) ? $number_month : 12;

                    // Trámite
                    if ($option_tramite === 1) {
                        $initial_admin_fee = (float) optional($goals->get('coste-tramite'))->amount_total;
                    }

                    // GPS
                    if ($option_gps === 1) {
                        $gps_tbl = $goals->get('gps');
                        $initial_gps = (float) $gps_tbl->amount_inicial;
                        $gps_amount_total = (float) $gps_tbl->amount_total;

                        $gps_quotes = $gps_amount_total - $initial_gps;
                        $gps_coutes = $meses_gps_seguro > 0 ? ($gps_quotes / $meses_gps_seguro) : 0;
                    } else {
                        $gps_coutes = 0;
                    }

                    // Seguro
                    if ($option_seguro === 1) {
                        $seguro_tbl = $goals->get('seguro');
                        $initial_insurance = (float) $seguro_tbl->amount_inicial;
                        $seguro_amount_total = (float) $seguro_tbl->amount_total;

                        $insurance_quotes = $seguro_amount_total - $initial_insurance;
                        $seguro_coutes = $meses_gps_seguro > 0 ? ($insurance_quotes / $meses_gps_seguro) : 0;
                    } else {
                        $seguro_coutes = 0;
                    }

                    // Porcentaje inicial
                    $initial_percentage = $product_mount > 0 ? (100 * $initial_amount) / $product_mount : 0;

                    // Inicial fraccionada
                    if ($type_initial === 2) {
                        $amount_fracction = (float) $request->input('amount_fracction', 0);
                        $mounth_fracction = (int) $request->input('mounth_fracction', 0);
                        $rate_fracction   = (float) $request->input('rate_fracction', 0);

                        $initial_cuotes = $this->calculateQuote($rate_fracction, $mounth_fracction, $amount_fracction);
                        $mount_aux = $initial_cuotes * $mounth_fracction;
                        $taxes_fraccion = round($mount_aux - $amount_fracction, 4);
                    }

                    // Monto financiado
                    $balance_to_financed = round($product_mount - $initial_amount, 4);

                    // Cuota francesa
                    $tasaMensual = ($annualRate / 100) / 12;
                    if ($number_month <= 0) {
                        throw new \Exception('Número de meses inválido.');
                    }

                    if ($tasaMensual > 0) {
                        $cuota = $balance_to_financed * ($tasaMensual * pow(1 + $tasaMensual, $number_month)) / (pow(1 + $tasaMensual, $number_month) - 1);
                    } else {
                        $cuota = $balance_to_financed / $number_month;
                    }

                    $amount_fraccion = round($cuota, 4);

                    // Cronograma (sin mutar el Carbon original)
                    $saldo = $balance_to_financed;
                    $baseDate = Carbon::now();

                    for ($i = 1; $i <= $number_month; $i++) {
                        $date_quota = $baseDate->copy()->addMonths($i)->format('Y-m-d');

                        $saldo_inicial = $saldo;
                        $interes = $saldo * $tasaMensual;
                        $amortizacion = $cuota - $interes;
                        $saldo = $saldo - $amortizacion;
                        if ($saldo < 0) { $saldo = 0; }

                        $initial_fraccion = ($type_initial === 2 && $i <= $mounth_fracction) ? $initial_cuotes : 0;
                        $gps = ($i <= $meses_gps_seguro) ? $gps_coutes : 0;
                        $seguro = ($i <= $meses_gps_seguro) ? $seguro_coutes : 0;

                        $json[] = [
                            'id'            => $i,
                            'date'          => $date_quota,
                            'saldo_inicial' => $saldo_inicial,
                            'amount'        => $amount_fraccion,
                            'capital'       => $amortizacion,
                            'interes'       => $interes,
                            'saldo_final'   => $saldo,
                            'total_pay'     => 0,
                            'status'        => 0,
                            'gps'           => $gps,
                            'seguro'        => $seguro,
                            'initial'       => $initial_fraccion,
                        ];
                    }

                    // Totales
                    $coste_prestamo = $amount_fraccion * $number_month;
                    $total_cost_loan = $coste_prestamo + $gps_quotes + $insurance_quotes; // antes: amount
                    $total_amount_interest = round($coste_prestamo - $balance_to_financed, 4); // antes: rate
                } else {
                    // Contado: mantener inicial en 0 para loan (como ya hacías)
                    $annualRate = 0;
                    $initial_amount = 0;
                }

                // Crear Loan (YA CON NOMBRES NUEVOS)
                Loan::create([
                    'customer_id'            => $customer_id,
                    'user_id'                => auth()->user()->id,
                    'business_id'            => $business_id,
                    'product_id'             => $product_id,
                    'status'                 => 'quotation',
                    'product_name'           => $product->name,
                    'date'                   => Carbon::now(),

                    // RENOMBRADOS
                    'customer_name'          => $customer_name,          // antes type_product
                    'type_quotation'         => $typeQuotation,          // antes period
                    'annual_interest_rate'   => $annualRate,             // antes multiplier
                    'total_amount_interest'  => $total_amount_interest,  // antes rate
                    'total_cost_loan'        => $total_cost_loan,        // antes amount
                    'balance_to_financed'    => $balance_to_financed,    // antes loan_amount
                    'initial_admin_fee'      => $initial_admin_fee,      // antes admin_fee
                    'initial_gps'            => $initial_gps,            // antes gps
                    'initial_insurance'      => $initial_insurance,      // antes insurance

                    // Se quedan igual
                    'number_month'           => $number_month,
                    'quotes'                 => json_encode($json),
                    'gps_quotes'             => $gps_quotes,
                    'insurance_quotes'       => $insurance_quotes,
                    'product_price'          => $product_mount,
                    'initial_percentage'     => $initial_percentage,
                    'initial_amount'         => $initial_amount,
                    'contact_source'         => $request->input('contact_source'),
                    'terms'                  => $terms,
                    'waiter'                 => $waiter,
                    'initial_fraction'       => $amount_fracction,
                    'mounth_initial'         => $mounth_fracction,
                    'start_rate'             => $taxes_fraccion,
                ]);

                return ['success' => true, 'msg' => 'successfully added'];
            });

        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line:'.$e->getLine().' Message:'.$e->getMessage());
            $output = ['success' => false, 'msg' => 'Error: '.$e->getLine().' Message: '.$e->getMessage()];
        }

        return redirect('loans-quotations')->with('status', $output);
    }


    
    public function show($id){
         $business_id = request()->session()->get('user.business_id');
        $type = request()->get('type');
        $loan = Loan::find($id);
        $detail = json_decode($loan->quotes);
        $customer = Contact::find($loan->customer_id);
		$user = User::find($loan->user_id);
        //Total a pagar - total
         $total = ($loan->type_quotation == 2)
            ? ($loan->total_cost_loan
                + $loan->initial_admin_fee
                + $loan->initial_gps
                + $loan->initial_insurance
                + $loan->initial_amount
                + $loan->start_rate)
            : $loan->product_price;

        return view('loan.quotation.show')->with(compact('detail','type','customer','loan','user','total'));
    
    }

    public function downloadPdf(Request $request)
     {
        $id = $request->id;
        $loan = Loan::find($id);
        $customer = Contact::find($loan->customer_id);
        $user = User::find($loan->user_id);
        $product = Product::find($loan->product_id);
        $variation = $product->variations->first();
        $images = Media::where('model_id',$variation->id)->where('model_type','App\Variation')->get();
        $quotes = json_decode($loan->quotes);
        //Calcular fecha de la vigencia
        $fecha = Carbon::parse($loan->date);
        $date = $fecha->isoFormat('D/MM/Y');
        $anio = $fecha->isoFormat('Y');
        $aux = $fecha->addDays(10);
        $date_valid = $aux->isoFormat('D/MM/Y');
         // Total a pagar
        $total = ($loan->type_quotation == 2)
            ? ($loan->total_cost_loan
                + $loan->initial_admin_fee
                + $loan->initial_gps
                + $loan->initial_insurance
                + $loan->initial_amount
                + $loan->start_rate)
            : $loan->product_price;

        $pdf = Pdf::set_option('isRemoteEnabled', true)->loadView('loan.quotation.pdf_format',compact('anio','loan','quotes','date','customer','user','product','total','date_valid','images'));
        return $pdf->download('cotizacion.pdf');
    }


    public function destroy($id)
    {
        if (! auth()->user()->can('loand_setting.access')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;
                $loan = Loan::where('business_id', $business_id)->findOrFail($id);
                if ($loan) {
                    $loan->delete();
                    $output = ['success' => true,'msg' => __('loand.deleted_success')];
                } else {
                    $output = ['success' => false,'msg' =>  __('lang_v1.loan_cannot_be_deleted')];
                }
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }
            return $output;
        }
    }


    public function report()
    {
        return view('loan.quotation.report');
    }

    private function calculateQuote($multiplayer, $number_month, $loan_amount){
        $tasaMensual = ($multiplayer / 100) / 12; 
        if ($tasaMensual > 0) {
            $cuota = $loan_amount * ($tasaMensual * pow(1 + $tasaMensual, $number_month)) / (pow(1 + $tasaMensual, $number_month) - 1);
        } else {
            $cuota = $loan_amount / $number_month; // Si la tasa es 0, simplemente dividir el monto total entre el número de meses
        }
        $amount_fraccion = round($cuota, 4);
        return  $amount_fraccion;
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
}
