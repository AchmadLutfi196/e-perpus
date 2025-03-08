<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrow;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BorrowController extends Controller
{
    public function index()
    {
        $borrows = Borrow::with(['user', 'book'])
            ->when(auth()->user()->hasRole('member'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->paginate(15);
            
        return response()->json($borrows);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'user_id' => 'required|exists:users,id',
            'due_date' => 'required|date|after:today',
            'notes' => 'nullable|string'
        ]);
        
        $book = Book::findOrFail($request->book_id);
        
        if ($book->copies_available <= 0) {
            return response()->json([
                'message' => 'Book is not available for borrowing.'
            ], 400);
        }
        
        $borrow = new Borrow([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id,
            'borrow_date' => Carbon::now(),
            'due_date' => Carbon::parse($request->due_date),
            'notes' => $request->notes,
            'status' => 'borrowed'
        ]);
        
        $borrow->save();
        
        // Decrease available copies
        $book->copies_available -= 1;
        $book->save();
        
        return response()->json($borrow, 201);
    }
    
    public function return(Borrow $borrow)
    {
        if ($borrow->status === 'returned') {
            return response()->json([
                'message' => 'This book has already been returned.'
            ], 400);
        }
        
        $borrow->return_date = Carbon::now();
        $borrow->status = 'returned';
        $borrow->save();
        
        // Increase available copies
        $book = $borrow->book;
        $book->copies_available += 1;
        $book->save();
        
        return response()->json($borrow);
    }
    
    public function renewLoan(Borrow $borrow, Request $request)
    {
        $request->validate([
            'due_date' => 'required|date|after:today',
        ]);
        
        if ($borrow->status === 'returned') {
            return response()->json([
                'message' => 'Cannot renew, this book has already been returned.'
            ], 400);
        }
        
        $borrow->due_date = Carbon::parse($request->due_date);
        $borrow->save();
        
        return response()->json($borrow);
    }
}