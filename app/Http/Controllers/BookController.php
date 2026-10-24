<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    /**
     * Menampilkan daftar buku.
     */
    public function index(Request $request)
    {
        $books = Book::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_buku', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhere('pengarang', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return inertia('Books/Index', [
            'books' => $books,
            'search' => $request->search,
        ]);
    }

    /**
     * Menampilkan form tambah buku.
     */
    public function create()
    {
        return inertia('Books/Create');
    }

    /**
     * Menyimpan buku baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_buku' => 'required|unique:books,kode_buku',
            'judul' => 'required|max:255',
            'pengarang' => 'required|max:255',
            'penerbit' => 'nullable|max:255',
            'tahun' => 'nullable|digits:4',
            'stok' => 'required|integer|min:0',
        ]);

        Book::create($validated);

        return redirect()
            ->route('books.index')
            ->with('success', 'Buku berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
        public function edit(Book $book) 
    { 
        // $book sudah otomatis berisi data buku yang akan diedit (hasil Route Model Binding) 
        // Kirim data tersebut ke halaman Vue 'Books/Edit' agar form bisa menampilkan nilai lama 
        return inertia('Books/Edit', [ 
            'book' => $book, 
        ]); 
    } 
    
    public function update(Request $request, Book $book) 
    { 
        // Aturan validasi sama seperti store() pada Pertemuan 6, dengan SATU perbedaan penting: 
        // aturan unique:books untuk kode_buku perlu mengizinkan kode milik buku ini sendiri, 
        // karena jika tidak, Laravel akan mengira kode_buku ini 'sudah dipakai' oleh dirinya sendiri 
        $validated = $request->validate([ 
            'kode_buku'  => 'required|unique:books,kode_buku,' . $book->id,  // kecualikan id buku ini 
            'judul'      => 'required|max:255', 
            'pengarang'  => 'required|max:255', 
            'penerbit'   => 'nullable|max:255', 
            'tahun'      => 'nullable|digits:4', 
            'stok'       => 'required|integer|min:0', 
        ]); 
    
        // Mengganti data lama pada $book dengan data baru yang sudah divalidasi 
        $book->update($validated); 
    
        // Kembali ke daftar buku, sertakan flash message (lihat Dasar Teori E.3) 
        return redirect()->route('books.index')->with('success', 'Buku berhasil diperbarui'); 
    }

    /**
     * Remove the specified resource from storage.
     */
        public function destroy(Book $book) 
    { 
    // Menghapus baris data buku ini secara permanen dari tabel books 
    $book->delete(); 
    // Kembali ke daftar buku, sertakan flash message 
    return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus'); 
    } 
}
