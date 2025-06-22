<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Bill;
use App\Models\BillParticipant;
use App\Models\User;
use App\Models\Expense;
use App\Models\ExpenseShare;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get all bills where user is either creator or participant
        $allBills = collect();
        
        // Add bills created by the user
        $createdBills = $user->createdBills()->with('participants.user')->latest()->get();
        $allBills = $allBills->merge($createdBills);
        
        // Add bills where user is a participant (excluding duplicates)
        $participatedBills = $user->bills()->with('creator')->latest()->get();
        $allBills = $allBills->merge($participatedBills);
        
        // Remove duplicates and sort by latest
        $allBills = $allBills->unique('id')->sortByDesc('created_at');
        
        return view('bills.index', compact('allBills'));
    }

    public function show(Bill $bill)
    {
        // Check if user has access to this bill
        $user = Auth::user();
        if (!$bill->participants()->where('user_id', $user->id)->exists() && $bill->created_by !== $user->id) {
            abort(403, 'You do not have access to this bill.');
        }
        
        $bill->load(['participants.user', 'creator', 'expenses.paidBy', 'expenses.participants']);
        
        return view('bills.show', compact('bill'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_name' => 'required|string|max:255',
            'participants' => 'required|array|min:1',
            'participants.*' => 'required|string|email',
        ]);

        try {
            DB::beginTransaction();

            // Create the bill
            $bill = Bill::create([
                'name' => $validated['bill_name'],
                'created_by' => Auth::id(),
                'status' => 'active'
            ]);

            // Get all participants including the creator
            $participantEmails = array_unique($validated['participants']);
            
            // Remove the current user's email from the list since they're automatically added
            $participantEmails = array_filter($participantEmails, function($email) {
                return $email !== Auth::user()->email;
            });
            
            // Add creator to participants list
            $participantEmails[] = Auth::user()->email;
            
            $participants = User::whereIn('email', $participantEmails)->get();
            
            // Check if all provided emails exist
            $foundEmails = $participants->pluck('email')->toArray();
            $missingEmails = array_diff($participantEmails, $foundEmails);
            
            if (!empty($missingEmails)) {
                $missingEmailsList = implode(', ', $missingEmails);
                throw new \Exception('The following participants are not registered: ' . $missingEmailsList . '. Please ask them to register first.');
            }

            // Ensure we have at least 2 participants (including creator)
            if ($participants->count() < 2) {
                throw new \Exception('At least 2 participants (including you) are required. Please add at least one other participant.');
            }

            // For now, we'll set equal amounts for all participants
            // Later this can be customized per participant
            $amountPerPerson = 0; // This will be set when items are added to the bill

            // Create bill participants
            foreach ($participants as $participant) {
                BillParticipant::create([
                    'bill_id' => $bill->id,
                    'user_id' => $participant->id,
                ]);
            }

            DB::commit();

            return redirect('/dashboard')->with('success', 'Bill "' . $bill->name . '" created successfully with ' . $participants->count() . ' participants!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create bill: ' . $e->getMessage()])->withInput();
        }
    }

    public function storeExpense(Request $request, Bill $bill)
    {
        // Check if user has access to this bill
        $user = Auth::user();
        if (!$bill->participants()->where('user_id', $user->id)->exists() && $bill->created_by !== $user->id) {
            abort(403, 'You do not have access to this bill.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'share_with_all' => 'boolean',
            'selected_participants' => 'required_if:share_with_all,false|array',
            'selected_participants.*' => 'exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            // Create the expense
            $expense = Expense::create([
                'bill_id' => $bill->id,
                'title' => $validated['title'],
                'amount' => $validated['amount'],
                'paid_by' => $user->id,
                'description' => $validated['description'] ?? null
            ]);

            // Determine which participants to share with
            $participantsToShare = [];
            
            if ($validated['share_with_all'] ?? false) {
                // Share with all participants
                $participantsToShare = $bill->participants->pluck('user_id')->toArray();
            } else {
                // Share with selected participants
                $participantsToShare = $validated['selected_participants'] ?? [];
            }

            // Create expense shares
            foreach ($participantsToShare as $participantId) {
                ExpenseShare::create([
                    'expense_id' => $expense->id,
                    'user_id' => $participantId
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expense added successfully!',
                'expense' => $expense->load('paidBy', 'participants')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add expense: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getExpenses(Bill $bill)
    {
        // Check if user has access to this bill
        $user = Auth::user();
        if (!$bill->participants()->where('user_id', $user->id)->exists() && $bill->created_by !== $user->id) {
            abort(403, 'You do not have access to this bill.');
        }

        $expenses = $bill->expenses()
            ->with(['paidBy', 'participants'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'expenses' => $expenses
        ]);
    }

    public function getBalanceSheet(Bill $bill)
    {
        // Check if user has access to this bill
        $user = Auth::user();
        if (!$bill->participants()->where('user_id', $user->id)->exists() && $bill->created_by !== $user->id) {
            abort(403, 'You do not have access to this bill.');
        }

        $expenses = $bill->expenses()->with(['paidBy', 'participants'])->get();
        $participants = $bill->participants()->with('user')->get();
        
        // Initialize balance tracking
        $balances = [];
        $totalSpent = [];
        $totalOwed = [];
        
        // Initialize all participants
        foreach ($participants as $participant) {
            $balances[$participant->user_id] = 0;
            $totalSpent[$participant->user_id] = 0;
            $totalOwed[$participant->user_id] = 0;
        }
        
        // Calculate balances from expenses
        foreach ($expenses as $expense) {
            $amount = $expense->amount;
            $participantCount = $expense->participants->count();
            $amountPerPerson = $amount / $participantCount;
            
            // Add what the payer spent
            $balances[$expense->paid_by] += $amount;
            $totalSpent[$expense->paid_by] += $amount;
            
            // Subtract what each participant owes
            foreach ($expense->participants as $participant) {
                $balances[$participant->id] -= $amountPerPerson;
                $totalOwed[$participant->id] += $amountPerPerson;
            }
        }
        
        // Prepare balance sheet data
        $balanceSheet = [];
        foreach ($participants as $participant) {
            $userId = $participant->user_id;
            $balanceSheet[] = [
                'user' => $participant->user,
                'total_spent' => $totalSpent[$userId],
                'total_owed' => $totalOwed[$userId],
                'balance' => $balances[$userId],
                'is_positive' => $balances[$userId] > 0
            ];
        }
        
        // Calculate settlements (who pays whom)
        $settlements = $this->calculateSettlements($balances);
        
        return response()->json([
            'success' => true,
            'balance_sheet' => $balanceSheet,
            'settlements' => $settlements,
            'total_expenses' => $expenses->sum('amount')
        ]);
    }

    private function calculateSettlements($balances)
    {
        $settlements = [];
        $balancesCopy = $balances;
        
        // Sort by balance (positive first, then negative)
        arsort($balancesCopy);
        
        $creditors = array_filter($balancesCopy, function($balance) {
            return $balance > 0.01; // Small threshold to avoid floating point issues
        });
        
        $debtors = array_filter($balancesCopy, function($balance) {
            return $balance < -0.01;
        });
        
        foreach ($creditors as $creditorId => $creditorAmount) {
            foreach ($debtors as $debtorId => $debtorAmount) {
                if (abs($debtorAmount) < 0.01) continue;
                
                $settlementAmount = min($creditorAmount, abs($debtorAmount));
                
                if ($settlementAmount > 0.01) {
                    $settlements[] = [
                        'from' => $debtorId,
                        'to' => $creditorId,
                        'amount' => $settlementAmount
                    ];
                    
                    $creditorAmount -= $settlementAmount;
                    $debtorAmount += $settlementAmount;
                    
                    if ($creditorAmount < 0.01) break;
                }
            }
        }
        
        return $settlements;
    }

    public function edit(Bill $bill)
    {
        // Check if user is the creator of this bill
        if ($bill->created_by !== Auth::id()) {
            abort(403, 'Only the bill creator can edit this bill.');
        }
        
        $bill->load(['participants.user']);
        
        return view('bills.edit', compact('bill'));
    }

    public function update(Request $request, Bill $bill)
    {
        // Check if user is the creator of this bill
        if ($bill->created_by !== Auth::id()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the bill creator can edit this bill.'
                ], 403);
            }
            abort(403, 'Only the bill creator can edit this bill.');
        }

        $validated = $request->validate([
            'bill_name' => 'required|string|max:255',
            'participants' => 'required|array|min:1',
            'participants.*' => 'required|string|email',
        ]);

        try {
            DB::beginTransaction();

            // Update the bill name
            $bill->update([
                'name' => $validated['bill_name']
            ]);

            // Get all participants including the creator
            $participantEmails = array_unique($validated['participants']);
            
            // Remove the current user's email from the list since they're automatically added
            $participantEmails = array_filter($participantEmails, function($email) {
                return $email !== Auth::user()->email;
            });
            
            // Add creator to participants list
            $participantEmails[] = Auth::user()->email;
            
            $participants = User::whereIn('email', $participantEmails)->get();
            
            // Check if all provided emails exist
            $foundEmails = $participants->pluck('email')->toArray();
            $missingEmails = array_diff($participantEmails, $foundEmails);
            
            if (!empty($missingEmails)) {
                $missingEmailsList = implode(', ', $missingEmails);
                throw new \Exception('The following participants are not registered: ' . $missingEmailsList . '. Please ask them to register first.');
            }

            // Ensure we have at least 2 participants (including creator)
            if ($participants->count() < 2) {
                throw new \Exception('At least 2 participants (including you) are required. Please add at least one other participant.');
            }

            // Remove existing participants
            $bill->participants()->delete();

            // Create new bill participants
            foreach ($participants as $participant) {
                BillParticipant::create([
                    'bill_id' => $bill->id,
                    'user_id' => $participant->id,
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bill "' . $bill->name . '" updated successfully!'
                ]);
            }

            return redirect('/bills')->with('success', 'Bill "' . $bill->name . '" updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update bill: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to update bill: ' . $e->getMessage()])->withInput();
        }
    }

    public function updateExpense(Request $request, Bill $bill, Expense $expense)
    {
        // Check if user has access to this bill
        $user = Auth::user();
        if (!$bill->participants()->where('user_id', $user->id)->exists() && $bill->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this bill.'
            ], 403);
        }

        // Check if user is the one who paid for this expense
        if ($expense->paid_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the person who paid for this expense can edit it.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'share_with_all' => 'boolean',
            'selected_participants' => 'required_if:share_with_all,false|array',
            'selected_participants.*' => 'exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            // Update the expense
            $expense->update([
                'title' => $validated['title'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null
            ]);

            // Remove existing expense shares
            $expense->shares()->delete();

            // Determine which participants to share with
            $participantsToShare = [];
            
            if ($validated['share_with_all'] ?? false) {
                // Share with all participants
                $participantsToShare = $bill->participants->pluck('user_id')->toArray();
            } else {
                // Share with selected participants
                $participantsToShare = $validated['selected_participants'] ?? [];
            }

            // Create new expense shares
            foreach ($participantsToShare as $participantId) {
                ExpenseShare::create([
                    'expense_id' => $expense->id,
                    'user_id' => $participantId
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully!',
                'expense' => $expense->load('paidBy', 'participants')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update expense: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteExpense(Request $request, Bill $bill, Expense $expense)
    {
        // Check if user has access to this bill
        $user = Auth::user();
        if (!$bill->participants()->where('user_id', $user->id)->exists() && $bill->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this bill.'
            ], 403);
        }

        // Check if user is the one who paid for this expense
        if ($expense->paid_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the person who paid for this expense can delete it.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Delete the expense (this will cascade delete expense shares)
            $expense->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete expense: ' . $e->getMessage()
            ], 500);
        }
    }
} 