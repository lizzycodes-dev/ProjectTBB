<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Purchase;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created expense.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'category'    => ['nullable', 'string', 'max:255'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'expense_date'=> ['required', 'date'],
            'notes'       => ['nullable', 'string'],
            'purchase_id' => ['nullable', 'integer', 'exists:purchases,id'],
        ]);

        Expense::create([
            'purchase_id' => $validated['purchase_id'] ?? null,
            'description' => $validated['description'],
            'category'    => $validated['category'] ?? null,
            'amount'      => $validated['amount'],
            'expense_date'=> $validated['expense_date'],
            'notes'       => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('purchases.index', ['tab' => 'expenses'])
            ->with('success', 'Expense recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        //
    }
}