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
}
