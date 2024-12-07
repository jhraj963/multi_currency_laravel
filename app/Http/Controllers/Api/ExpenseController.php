<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'currency' => 'required|string|in:USD,EUR,BDT,JPY',
        ]);

        $expense = Expense::create($validated);
        return response()->json(['message' => 'Expense added successfully', 'data' => $expense], 201);
    }

    public function index()
    {
        $expenses = Expense::all();
        return response()->json($expenses);
    }

    public function summary(Request $request)
    {
        $baseCurrency = $request->query('baseCurrency', 'USD');
        $exchangeRates = [
            "USD" => 1,
            "EUR" => 0.91,
            "BDT" => 109.5,
            "JPY" => 140
        ];

        if (!isset($exchangeRates[$baseCurrency])) {
            return response()->json(['message' => 'Unsupported base currency'], 400);
        }

        $expenses = Expense::all();
        $total = 0;

        foreach ($expenses as $expense) {
            $convertedAmount = $expense->amount / $exchangeRates[$expense->currency] * $exchangeRates[$baseCurrency];
            $total += round($convertedAmount, $expense->currency == 'JPY' ? 0 : 2);
        }

        return response()->json(['baseCurrency' => $baseCurrency, 'total' => $total]);
    }
}
