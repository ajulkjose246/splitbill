<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'participants' => 'required|array|min:2',
            'participants.*' => 'required|string|email',
        ]);

        // For now, we'll just redirect back with a success message
        // Later we'll implement the actual bill creation logic
        return redirect('/dashboard')->with('success', 'Bill created successfully!');
    }
} 