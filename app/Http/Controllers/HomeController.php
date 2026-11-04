<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        $user = request()->user();
        $totalIncome = $user->incomes()->sum('amount');
        $totalExpense = $user->expenses()->sum('amount');
        $balance = $totalIncome - $totalExpense;

        return view('home', compact('totalIncome', 'totalExpense', 'balance'));
    }

    public function walletTransactions()
    {
        $user = request()->user();
        $incomes = $user->incomes()->orderByDesc('date')->orderByDesc('id')->get();
        $expenses = $user->expenses()->orderByDesc('date')->orderByDesc('id')->get();

        return view('wallet.transactions', compact('incomes', 'expenses'));
    }
}
