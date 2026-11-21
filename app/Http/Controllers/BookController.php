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
                        ->orWhere('pengarang', 'like', "%{$search}%")
                        ->orWhere('penerbit', 'like', "%{$search}%");
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
     * Endpoint JSON untuk live search buku (dipanggil oleh Axios).
     */
    public function search(Request $request)
    {
        $kataKunci = $request->query('q', '');

        $books = Book::where(function ($query) use ($kataKunci) {
            $query->where('kode_buku', 'like', "%{$kataKunci}%")
                ->orWhere('judul', 'like', "%{$kataKunci}%")
                ->orWhere('pengarang', 'like', "%{$kataKunci}%")
                ->orWhere('penerbit', 'like', "%{$kataKunci}%");
        })
            ->latest()
            ->limit(10)
            ->get([
                'id',
                'kode_buku',
                'judul',
                'pengarang',
                'penerbit',
                'stok',
            ]);

        return response()->json($books);
    }

    /**
     * Endpoint untuk memeriksa ketersediaan kode buku secara realtime.
     */
    public function cekKode(Request $request)
    {
        $kode = $request->query('kode', '');

        $sudahDipakai = Book::where('kode_buku', $kode)->exists();

        return response()->json([
            'tersedia' => ! $sudahDipakai,
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
     * Menampilkan form edit buku.
     */
    public function edit(Book $book)
    {
        return inertia('Books/Edit', [
            'book' => $book,
        ]);
    }

    /**
     * Memperbarui data buku.
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'kode_buku' => 'required|unique:books,kode_buku,' . $book->id,
            'judul' => 'required|max:255',
            'pengarang' => 'required|max:255',
            'penerbit' => 'nullable|max:255',
            'tahun' => 'nullable|digits:4',
            'stok' => 'required|integer|min:0',
        ]);

        $book->update($validated);

        return redirect()
            ->route('books.index')
            ->with('success', 'Buku berhasil diperbarui');
    }

    /**
     * Menghapus data buku.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', 'Buku berhasil dihapus');
    }
}
