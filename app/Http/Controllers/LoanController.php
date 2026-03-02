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
use App\Goal;
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

  public function getPrices(Request $request){
        $options = '';
        $options .= '<option selected disabled >Selecciona un precio</option>';
        $variations = Variation::where('product_id',$request->id)->get();
        foreach ($variations as $key => $variation) {
            $options .= "<option  value='".$variation->sell_price_inc_tax."'> ".  number_format($variation->sell_price_inc_tax, 2) ." </option>";
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
