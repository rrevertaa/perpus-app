<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController; 
use App\Http\Controllers\BookController;   // baris baru: memanggil BookController 
use App\Http\Controllers\MemberController;
use App\Http\Controllers\LoanController; 
use App\Http\Controllers\DashboardController;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index']); 
Route::get('/tentang', [HomeController::class, 'tentang']); 
Route::get('/kontak', [HomeController::class, 'kontak']); 
  
// Blok baru: seluruh route di dalamnya hanya bisa diakses setelah login 

Route::middleware(['auth'])->group(function () {

    // Route AJAX (JSON) HARUS ditulis DI ATAS Route::resource (lihat E.5)
    Route::get('books/search', [BookController::class, 'search'])
        ->name('books.search');
    Route::get('members/search', [MemberController::class, 'search'])
        ->name('members.search');
    Route::get('members/cek-nis', [MemberController::class, 'cekNis'])
        ->name('members.cek-nis');
    Route::get('/books/cek-kode', [BookController::class, 'cekKode'])
        ->name('books.cek-kode');

    Route::resource('books', BookController::class);
    Route::resource('members', MemberController::class);
    Route::resource('loans', LoanController::class)
        ->only(['index', 'create', 'store']);
    Route::patch('loans/{loan}/kembalikan', [LoanController::class, 'kembalikan'])
        ->name('loans.kembalikan');
});


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
