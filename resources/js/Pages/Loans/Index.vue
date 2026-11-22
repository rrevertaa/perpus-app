<script setup>
// ref = membuat variabel reaktif: jika nilainya berubah, tampilan ikut berubah
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// Link = tautan Inertia (tanpa reload halaman)
// router = alat untuk mengirim permintaan dari kode JavaScript
import { Link, router } from '@inertiajs/vue3';

// 'loans' diterima dari LoanController@index (peminjaman + anggota + buku)
defineProps({
    loans: Object,
});

// true selama permintaan pengembalian sedang dikirim. Dipakai untuk
// menonaktifkan tombol agar tidak tertekan dua kali.
const memproses = ref(false);

// Mengubah angka 5000 menjadi teks "Rp5.000"
function formatRupiah(angka) {
    // Number() memastikan nilainya angka. toLocaleString('id-ID') memberi
    // titik pemisah ribuan sesuai kebiasaan Indonesia.
    return 'Rp' + Number(angka).toLocaleString('id-ID');
}

// Dipanggil saat tombol "Kembalikan" ditekan.
// Parameter 'loan' adalah satu baris peminjaman.
function kembalikan(loan) {
    // confirm() menampilkan kotak Ya/Batal. Jika Batal, hentikan fungsi.
    if (!confirm('Proses pengembalian buku "' + loan.book.judul + '"?')) {
        return;
    }

    // Kirim permintaan PATCH ke route loans.kembalikan, dengan id peminjaman.
    // Argumen kedua {} adalah data yang dikirim (kosong, kita hanya butuh id).
    router.patch(route('loans.kembalikan', loan.id), {}, {
        // Posisi scroll halaman tidak kembali ke atas setelah selesai
        preserveScroll: true,
        // Saat permintaan mulai dikirim, nonaktifkan tombol
        onStart: () => { memproses.value = true; },
        // Saat selesai (berhasil maupun gagal), aktifkan kembali tombol
        onFinish: () => { memproses.value = false; },
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold">Data Peminjaman</h1>

            <!-- Pesan sukses dari session (dikirim oleh
                 back()->with('success', ...) di controller) -->
            <div v-if="$page.props.flash.success" class="my-2 text-green-700">
                {{ $page.props.flash.success }}
            </div>

            <!-- Pesan error dari back()->withErrors(['loan' => ...]) -->
            <div v-if="$page.props.errors.loan" class="my-2 text-red-600">
                {{ $page.props.errors.loan }}
            </div>

            <div class="my-3">
                <!-- Link menuju form peminjaman (Loans/Create.vue) -->
                <Link href="/loans/create">+ Pinjam Buku</Link>
            </div>

            <table class="border-collapse">
                <thead>
                    <tr>
                        <th class="border px-3 py-2">Anggota</th>
                        <th class="border px-3 py-2">Buku</th>
                        <th class="border px-3 py-2">Tanggal Pinjam</th>
                        <th class="border px-3 py-2">Jatuh Tempo</th>
                        <!-- Kolom baru: tanggal buku benar-benar dikembalikan -->
                        <th class="border px-3 py-2">Tanggal Kembali</th>
                        <th class="border px-3 py-2">Status</th>
                        <!-- Kolom baru: denda keterlambatan -->
                        <th class="border px-3 py-2">Denda</th>
                        <!-- Kolom baru: tombol untuk memproses pengembalian -->
                        <th class="border px-3 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Satu baris untuk setiap peminjaman pada halaman ini -->
                    <tr v-for="loan in loans.data" :key="loan.id">
                        <!-- loan.member dan loan.book tersedia karena
                             with() di controller -->
                        <td class="border px-3 py-2">
                            {{ loan.member.nama }}
                        </td>
                        <td class="border px-3 py-2">
                            {{ loan.book.judul }}
                        </td>
                        <!-- whitespace-nowrap = tanggal tidak dipecah
                             menjadi dua baris -->
                        <td class="border whitespace-nowrap px-3 py-2">
                            {{ loan.tanggal_pinjam }}
                        </td>
                        <td class="border whitespace-nowrap px-3 py-2">
                            {{ loan.tanggal_kembali_rencana }}
                        </td>
                        <!-- Jika tanggal_kembali masih kosong (null),
                             tampilkan tanda "-" -->
                        <td class="border whitespace-nowrap px-3 py-2">
                            {{ loan.tanggal_kembali || '-' }}
                        </td>
                        <td class="border px-3 py-2">
                            <!-- Kuning jika masih dipinjam, hijau jika
                                 sudah dikembalikan -->
                            <span
                                :class="loan.status === 'dipinjam'
                                    ? 'text-yellow-700'
                                    : 'text-green-700'"
                            >
                                {{ loan.status }}
                            </span>
                        </td>
                        <td class="border px-3 py-2">
                            <!-- Kasus 1: sudah dikembalikan, tampilkan denda
                                 yang tersimpan di database -->
                            <span v-if="loan.status === 'dikembalikan'">
                                {{ formatRupiah(loan.denda) }}
                            </span>
                            <!-- Kasus 2: masih dipinjam dan sudah lewat
                                 jatuh tempo -->
                            <span
                                v-else-if="loan.hari_terlambat > 0"
                                class="text-red-600"
                            >
                                Terlambat {{ loan.hari_terlambat }} hari
                                ({{ formatRupiah(loan.perkiraan_denda) }})
                            </span>
                            <!-- Kasus 3: masih dipinjam, belum terlambat -->
                            <span v-else>-</span>
                        </td>
                        <td class="border px-3 py-2">
                            <!-- Tombol hanya muncul untuk peminjaman yang
                                 belum dikembalikan -->
                            <button
                                v-if="loan.status === 'dipinjam'"
                                type="button"
                                :disabled="memproses"
                                class="text-blue-700 underline"
                                @click="kembalikan(loan)"
                            >
                                Kembalikan
                            </button>
                            <span v-else>-</span>
                        </td>
                    </tr>
                    <!-- Pesan jika belum ada data (colspan = jumlah kolom) -->
                    <tr v-if="loans.data.length === 0">
                        <td colspan="8" class="border px-3 py-2">
                            Belum ada data peminjaman.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Tautan paginasi, pola sama dengan halaman Buku dan Anggota -->
            <div class="mt-3 flex gap-3">
                <template v-for="link in loans.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" v-html="link.label" />
                    <span v-else v-html="link.label" class="text-gray-400" />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
