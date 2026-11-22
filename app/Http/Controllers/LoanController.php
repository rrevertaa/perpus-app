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

        /**
         * Memproses pengembalian buku dan menghitung denda.
         * Parameter Loan $loan diisi otomatis oleh Laravel (route model binding):
         * Laravel membaca {loan} pada URL, lalu mencari baris loans yang id-nya
         * sama dengan angka tersebut.
         */
        public function kembalikan(Loan $loan)
        {
            // 1. Tolak jika buku ini sudah dikembalikan sebelumnya.
            // Hal ini bisa terjadi jika tombol ditekan dua kali.
            if ($loan->status === 'dikembalikan') {
                // back() = kembali ke halaman sebelumnya, membawa pesan error
                // dengan kunci 'loan' (dibaca Vue sebagai errors.loan)
                return back()->withErrors([
                    'loan' => 'Buku ini sudah dikembalikan sebelumnya.',
                ]);
            }

            // 2. Hitung denda SEBELUM data diubah.
            // Selama tanggal_kembali masih kosong, atribut virtual memakai hari ini sebagai pembanding.
            $hariTerlambat = $loan->hari_terlambat;
            $denda = $loan->perkiraan_denda;

            // 3. Dua pekerjaan yang harus berhasil bersama-sama atau gagal bersama-sama:
            // (a) mengubah data peminjaman, (b) menambah stok buku.
            // "use ($loan, $denda)" memasukkan variabel luar ke dalam fungsi.
            DB::transaction(function () use ($loan, $denda) {
                // (a) Tandai selesai, catat tanggal kembali (hari ini) dan denda.
                // Denda disimpan agar nilainya tetap walau tarif berubah.
                $loan->update([
                    'status' => 'dikembalikan',
                    'tanggal_kembali' => today()->toDateString(),
                    'denda' => $denda,
                ]);

                // (b) Buku kembali ke rak, jadi stok bertambah 1
                $loan->book->increment('stok');
            });

            // 4. Susun pesan sukses. Kalimat denda hanya ditambahkan jika ada.
            $pesan = 'Buku berhasil dikembalikan. Stok buku bertambah 1.';

            if ($denda > 0) {
                // number_format(angka, jumlah desimal, pemisah desimal, pemisah ribuan)
                $pesan .= ' Terlambat '.$hariTerlambat.' Hari, denda Rp'
                    .number_format($denda, 0, ',', '.').'.';
            }

            // 5. Kembali ke halaman daftar peminjaman dengan pesan sukses
            return back()->with('success', $pesan);
        }
}

