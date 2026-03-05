<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\ExchangeRates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetExchageRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:exchangerate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'consultar tipo de cambio en api y guarda en la tabla correspondiente';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            DB::commit();
                $date = carbon::now()->format('y-m-d');
                $params = json_encode([
                    'fecha' => $date
                ]);
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => "https://apiperu.dev/api/tipo_de_cambio",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_POSTFIELDS => $params,        
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json',
                        'Content-Type: application/json',
                        'Authorization:'. ' Bearer ' .config('services.apiperutipocambio.token')
                    ],
                ]);
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                $data = json_decode($response, true);
                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    ExchangeRates::create([
                        'base_currency'=> 'PE',
                        'target_currency'=> $data["data"]["moneda"],
                        'purchase'=> $data["data"]["compra"],
                        'sale'=> $data["data"]["venta"],
                        'search_date'=> $data["data"]["fecha_busqueda"],
                    ]);
                    Log::info('TIPO DE CAMBIO CREADO CON EXITO EL: ' . now());
                }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('ERRO EN GENERAR LA TASA DE CAMBIO,  File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            exit('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
        }
    }
}
