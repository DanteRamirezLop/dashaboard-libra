<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LoanSetting;

class LoanSettingsController extends Controller
{
    public function index()
    {
        $percentages = []; //Porcentajes de la inicial
        $business_id = request()->session()->get('user.business_id');
        $type = request()->get('type');
        $terms = LoanSetting::first();
        $goalInitial = LoanSetting::where('name','initial')->first();
        if($goalInitial->description){
            $percentages = json_decode($goalInitial->description);
        }
        $loanSettings = LoanSetting::orderBy('id', 'asc')->skip(2)->take(10)->get();
        return view('loan.setting.index', compact('percentages', 'terms', 'loanSettings'));
    }


    public function termsUpdate(Request $request){
        try {
            $items = LoanSetting::find($request->id);
            $items->amount_total = $request->amount_total;
            $items->amount_inicial = $request->amount_inicial;
            $items->description = $request->description;
            $items->save();

            $output = ['success' => true,'msg' => 'Actualizado',];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = ['success' => false,'msg' => 'Error: '.$e->getLine().'Message:'.$e->getMessage(),];
        }
        return $output;
    }


    //  public function termsUpdate(Request $request){
    //     //FALTA VALIDAR LOS NUMERO REPETIDOS
    //     $percentages = [];
    //     $goalInitial = LoanSetting::where('name','initial')->first();
    //     if($goalInitial->description){
    //         $percentages = json_decode($goalInitial->description);
    //     }

    //     if($request->type == "add"){
    //         array_push($percentages, $request->value);
    //     }else{
    //         unset($percentages[$request->value]);
    //     }
        
    //     //antes de guardar mantener el formato []
    //     $arrayPercentages= array_values($percentages);
    //     $goalInitial->description = json_encode($arrayPercentages);
    //     $goalInitial->save();

    //     return response()->json(['status' => true, 'msg' => "Porcentaje de la inicial actulizado", 'values'=>$percentages]);
    // }
    
}
