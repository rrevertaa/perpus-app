<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard.
     */
    public function index()
    {
        $totalBuku = Book::count();
        $totalStok = Book::sum('stok');
        $totalAnggota = Member::count();

        $peminjamanAktif = Loan::where('status', 'dipinjam')->count();

        $peminjamanTerlambat = Loan::where('status', 'dipinjam')
            ->whereDate('tanggal_kembali_rencana', '<', today())
            ->count();

        $totalDenda = Loan::sum('denda');

        // Menghitung jumlah peminjaman hari ini
        $peminjamanHariIni = Loan::where(
            'tanggal_pinjam',
            today()->toDateString()
        )->count();

        $labels = [];
        $dataPeminjaman = [];

        // Menampilkan data peminjaman 12 bulan terakhir
        for ($i = 11; $i >= 0; $i--) {
            $awal = today()->startOfMonth()->subMonths($i);
            $akhir = $awal->copy()->endOfMonth();

            $labels[] = $awal->translatedFormat('M Y');

            $dataPeminjaman[] = Loan::whereBetween('tanggal_pinjam', [
                $awal->toDateString(),
                $akhir->toDateString()
            ])->count();
        }

        return inertia('Dashboard', [
            'statistik' => [
                'totalBuku' => $totalBuku,
                'totalStok' => $totalStok,
                'totalAnggota' => $totalAnggota,
                'peminjamanAktif' => $peminjamanAktif,
                'peminjamanTerlambat' => $peminjamanTerlambat,
                'totalDenda' => $totalDenda,
                'peminjamanHariIni' => $peminjamanHariIni,
            ],
            'grafik' => [
                'labels' => $labels,
                'dataPeminjaman' => $dataPeminjaman,
            ],
        ]);
    }
}
