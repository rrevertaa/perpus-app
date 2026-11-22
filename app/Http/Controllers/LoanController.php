<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Menampilkan daftar peminjaman.
     */
    public function index()
    {
        // with(['member', 'book']) mengambil data anggota dan buku sekaligus,
        // sehingga Vue dapat menampilkan nama anggota dan judul buku
        $loans = Loan::with(['member', 'book'])
            ->orderByDesc('id')
            ->paginate(10);

        return inertia('Loans/Index', [
            'loans' => $loans,
        ]);
    }

    /**
     * Menampilkan form peminjaman.
     */
    public function create()
    {
        // Semua anggota, diurutkan berdasarkan nama
        $members = Member::orderBy('nama')->get(['id', 'nama', 'nis_nim']);

        // Hanya buku yang stoknya masih ada
        $books = Book::where('stok', '>', 0)
            ->orderBy('judul')
            ->get(['id', 'judul', 'stok']);

        return inertia('Loans/Create', [
            'members' => $members,
            'books' => $books,
        ]);
    }

    /**
     * Menyimpan transaksi peminjaman.
     */
    public function store(Request $request)
    {
        // 1. Validasi: anggota dan buku wajib dipilih dan harus ada di database
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
        ], [
            'member_id.required' => 'Anggota wajib dipilih.',
            'member_id.exists' => 'Anggota yang dipilih tidak ditemukan.',
            'book_id.required' => 'Buku wajib dipilih.',
            'book_id.exists' => 'Buku yang dipilih tidak ditemukan.',
        ]);

        // 2. Periksa batas maksimal peminjaman anggota
        $jumlahDipinjam = Loan::where('member_id', $validated['member_id'])
            ->where('status', 'dipinjam')
            ->count();

        if ($jumlahDipinjam >= 3) {
            return back()->withErrors([
                'member_id' => 'Anggota sudah mencapai batas maksimal 3 buku yang sedang dipinjam.',
            ]);
        }

        // 3. Periksa stok di server. Dropdown hanya menampilkan buku bersetok,
        //    tetapi stok bisa habis sesaat setelah halaman dibuka petugas lain.
        $book = Book::findOrFail($validated['book_id']);

        if ($book->stok < 1) {
            return back()->withErrors([
                'book_id' => 'Stok buku ini sudah habis. Pilih buku lain.',
            ]);
        }

        // 4. Simpan peminjaman dan kurangi stok sebagai satu kesatuan.
        //    Jika salah satu gagal, keduanya dibatalkan.
        DB::transaction(function () use ($validated, $book) {
            Loan::create([
                'member_id' => $validated['member_id'],
                'book_id' => $validated['book_id'],
                'tanggal_pinjam' => now()->toDateString(),
                'tanggal_kembali_rencana' => now()->addDays(14)->toDateString(),
                'status' => 'dipinjam',
            ]);

            $book->decrement('stok');
        });

        // 5. Kembali ke daftar peminjaman dengan pesan sukses
        return redirect()->route('loans.index')
            ->with('success', 'Peminjaman berhasil disimpan. Stok buku berkurang 1.');
    }
}
