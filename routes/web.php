<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\BookReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rute publik/landing page
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard setelah login
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rute profil pengguna
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rute Perpustakaan Digital
Route::middleware(['auth', 'verified'])->group(function () {
    // Katalog Buku (dapat diakses semua pengguna yang login)
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
    Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
    Route::get('/authors/{author}', [AuthorController::class, 'show'])->name('authors.show');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    
    // Review buku (untuk semua pengguna)
    Route::get('/books/{book}/reviews', [BookReviewController::class, 'index'])->name('books.reviews.index');
    Route::post('/books/{book}/reviews', [BookReviewController::class, 'store'])->name('books.reviews.store');
    Route::put('/reviews/{review}', [BookReviewController::class, 'update'])->name('books.reviews.update');
    Route::delete('/reviews/{review}', [BookReviewController::class, 'destroy'])->name('books.reviews.destroy');
    
    // Manajemen peminjaman (untuk member)
    Route::get('/borrows', [BorrowController::class, 'index'])->name('borrows.index');
    Route::get('/borrows/{borrow}', [BorrowController::class, 'show'])->name('borrows.show');
    
    // Rute untuk admin dan pustakawan
    Route::middleware('role:admin|librarian')->group(function () {
        // Manajemen Buku
        Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
        Route::post('/books', [BookController::class, 'store'])->name('books.store');
        Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
        Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
        
        // Manajemen Penulis
        Route::get('/authors/create', [AuthorController::class, 'create'])->name('authors.create');
        Route::post('/authors', [AuthorController::class, 'store'])->name('authors.store');
        Route::get('/authors/{author}/edit', [AuthorController::class, 'edit'])->name('authors.edit');
        Route::put('/authors/{author}', [AuthorController::class, 'update'])->name('authors.update');
        Route::delete('/authors/{author}', [AuthorController::class, 'destroy'])->name('authors.destroy');
        
        // Manajemen Kategori
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        
        // Manajemen Peminjaman
        Route::post('/borrows', [BorrowController::class, 'store'])->name('borrows.store');
        Route::put('/borrows/{borrow}/return', [BorrowController::class, 'return'])->name('borrows.return');
        Route::put('/borrows/{borrow}/renew', [BorrowController::class, 'renewLoan'])->name('borrows.renew');
    });
    
    // Rute khusus admin
    Route::middleware('role:admin')->group(function () {
        // Manajemen Pengguna
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role.update');
        
        // Laporan dan Statistik
        Route::get('/reports/borrows', [BorrowController::class, 'report'])->name('reports.borrows');
        Route::get('/reports/books', [BookController::class, 'report'])->name('reports.books');
        Route::get('/reports/users', [UserController::class, 'report'])->name('reports.users');
    });
});

require __DIR__.'/auth.php';