<?php

namespace App\Http\Controllers;

use App\Http\Resources\CashResource;
use App\Models\Cash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CashController extends Controller
{
    public function index()
    {
        date_default_timezone_set("Asia/Jakarta");
        $from = request('from');
        $to   = request('to') . " " . date("H:i:s");
        if($from && $to)
        {
            $debit = $this->getBalances($from, $to, '>=');
            $credit = $this->getBalances($from, $to, '<');

            $transaction = Auth::user()->cashes()->whereBetween('when', [ $from, $to ])->latest()->get();
        }else{
            $debit = $this->getBalances(now()->firstOfMonth(), now(), '>=');
            $credit = $this->getBalances(now()->firstOfMonth(), now(), '<');
                        
            $transaction = Auth::user()->cashes()->whereBetween('when', [ now()->firstOfMonth(), now() ])->latest()->get();    
        }
        $transactions = Auth::user()->cashes()->get();
         return response()->json([
            'balances' => formatPrice(Auth::user()->cashes()->get('amount')->sum('amount')),
            'debit' => formatPrice($debit),
            'credit' => formatPrice($credit),
            'transaction' => CashResource::collection($transaction),
            'now' => now()->format('Y-m-d'),
            'firstOfMonth' => now()->firstOfMonth()->format('Y-m-d'),
            'transactions' => $transactions,
            'to' => $to
         ]);
    }
    public function store()
    {
        date_default_timezone_set("Asia/Jakarta");
        request()->validate([
            'name' => 'required',
            'amount' => 'required|numeric'
        ]);
        $when = request('when') ?? now();
        $slug = request('name') . "-" . Str::random(6);
        $cash = Auth::user()->cashes()->create([
            'name' => request('name'),
            'slug' => Str::slug($slug),
            'when' => $when,
            'amount' => request('amount'),
            'description' => request('description')
        ]);

        return response()->json([
            'message' => 'The Transaction has been saved. ',
            'cash' => new CashResource($cash)
        
        ]);
    }    

    public function show(Cash $cash)
    {
        $this->authorize('show');
        return new CashResource($cash);
    }

    public function getBalances($from, $to, $operator)
    {
        return Auth::user()->cashes()
                            ->whereBetween('when', [ $from, $to ])
                            ->where('amount', $operator , 0)
                            ->get('amount')
                            ->sum('amount');

    }
}
