<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration: menambah dua kolom baru pada tabel loans.
     */
    public function up(): void
    {
        // Schema::table (bukan Schema::create) karena tabel loans SUDAH ada.
        // Kita hanya mengubah strukturnya, bukan membuatnya dari awal.
        Schema::table('loans', function (Blueprint $table) {
            // Kolom tanggal_kembali: tanggal buku benar-benar dikembalikan
            $table->date('tanggal_kembali')
                // nullable() = boleh kosong (NULL). Buku yang masih dipinjam
                // belum punya tanggal kembali, jadi kolom harus boleh kosong.
                ->nullable()
                // after(...) = letakkan tepat setelah kolom ini
                ->after('tanggal_kembali_rencana');

            // Kolom denda: jumlah denda dalam rupiah.
            // unsignedInteger = bilangan bulat tanpa tanda minus
            $table->unsignedInteger('denda')
                // default(0) = baris lama dan peminjaman baru otomatis 0
                ->default(0)
                ->after('status');
        });
    }

    /**
     * Membatalkan migration (dipakai oleh php artisan migrate:rollback).
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Hapus dua kolom yang tadi ditambahkan
            $table->dropColumn(['tanggal_kembali', 'denda']);
        });
    }
};