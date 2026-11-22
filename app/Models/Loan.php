<?php

namespace App\Models;

// Carbon: kelas bantu Laravel untuk menghitung tanggal (selisih hari, dll.)
use Illuminate\Support\Carbon;

// Attribute: dipakai untuk membuat "atribut virtual" (accessor) pada model
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    // Mengaktifkan fitur factory (membuat data contoh) pada model ini
    use HasFactory;

    // Aturan denda perpustakaan: Rp1.000 untuk setiap hari keterlambatan.
    // Disimpan sebagai konstanta agar angkanya hanya ditulis di SATU tempat.
    // Jika aturan berubah, cukup ubah angka ini.
    public const DENDA_PER_HARI = 2000;

    // Kolom yang boleh diisi lewat create() dan update().
    // Kolom yang tidak terdaftar di sini akan ditolak oleh Laravel.
    protected $fillable = [
        'book_id',
        'member_id',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        // Dua kolom baru dari migration Pertemuan 12:
        'tanggal_kembali',
        'status',
        'denda',
    ];

    // Atribut virtual di bawah ini ikut dikirim ke Vue setiap kali data Loan
    // diubah menjadi JSON.
    protected $appends = ['hari_terlambat', 'perkiraan_denda'];

    public function book()
    {
        // Satu peminjaman dimiliki oleh satu buku (kolom book_id)
        return $this->belongsTo(Book::class);
    }

    public function member()
    {
        // Satu peminjaman dimiliki oleh satu anggota (kolom member_id)
        return $this->belongsTo(Member::class);
    }

    /**
     * Atribut virtual: berapa hari peminjaman ini terlambat.
     * Dipanggil sebagai $loan->hari_terlambat (huruf kecil, garis bawah).
     */
    protected function hariTerlambat(): Attribute
    {
        // get: berisi fungsi yang dijalankan setiap kali atribut ini dibaca
        return Attribute::make(get: function () {
            // Jika tanggal jatuh tempo belum tersedia, tidak ada keterlambatan
            if (!$this->tanggal_kembali_rencana) {
                return 0;
            }

            // Ubah teks tanggal jatuh tempo menjadi objek Carbon.
            $jatuhTempo = Carbon::parse($this->tanggal_kembali_rencana)
                ->startOfDay();

            // Tanggal pembanding:
            // - sudah dikembalikan: pakai tanggal kembali yang sebenarnya
            // - belum dikembalikan: pakai hari ini
            $acuan = $this->tanggal_kembali
                ? Carbon::parse($this->tanggal_kembali)->startOfDay()
                : today();

            // Jika acuan belum melewati jatuh tempo, berarti tidak terlambat
            if ($acuan->lessThanOrEqualTo($jatuhTempo)) {
                return 0;
            }

            // Selisih hari antara jatuh tempo dan acuan.
            return (int) $jatuhTempo->diffInDays($acuan);
        });
    }

    /**
     * Atribut virtual: denda (rupiah) = hari terlambat x tarif per hari.
     * Dipanggil sebagai $loan->perkiraan_denda.
     */
    protected function perkiraanDenda(): Attribute
    {
        // $this->hari_terlambat memanggil atribut virtual di atas
        return Attribute::make(
            get: fn () => $this->hari_terlambat * self::DENDA_PER_HARI
        );
    }
}
