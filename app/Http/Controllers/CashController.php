<?php

namespace App\Http\Controllers;

use App\Http\Resources\CashResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CashController extends Controller
{
    public function index(){
         $debit = Auth::user()->cashes()->whereBetween('when', [ now()->firstOfMonth(), now() ])->where('amount','>=', 0)->get('amount')->sum('amount');
         $credit = Auth::user()->cashes()->where('amount','<', 0)->whereBetween('when', [ now()->firstOfMonth(), now() ])->get('amount')->sum('amount');
         $balances = Auth::user()->cashes()->get('amount')->sum('amount');
         $transaction = Auth::user()->cashes()->whereBetween('when', [ now()->firstOfMonth(), now() ])->latest()->get();
         return response()->json([
            'balances' => formatPrice($balances),
            'debit' => formatPrice($debit),
            'credit' => formatPrice($credit),
            'transaction' => CashResource::collection($transaction) 
         ]);
    }
    public function store()
    {
        request()->validate([
            'name' => 'required',
            'amount' => 'required|numeric'
        ]);
        $when = request('when') ?? now();
        $slug = request('name') . "-" . Str::random(6);
        Auth::user()->cashes()->create([
            'name' => request('name'),
            'slug' => Str::slug($slug),
            'when' => $when,
            'amount' => request('amount'),
            'description' => request('description')
        ]);

        return response()->json([
            'message' => 'The Transaction has been saved. '
        ]);
    }    
}
