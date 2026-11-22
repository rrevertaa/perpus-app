<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// BarChart = komponen grafik yang baru dibuat (Components/BarChart.vue)
import BarChart from '@/Components/BarChart.vue';
import { Head } from '@inertiajs/vue3';

// Data dari DashboardController@index:
// statistik = angka-angka kartu, grafik = label dan jumlah per bulan
defineProps({
    statistik: Object,
    grafik: Object,
});

// Mengubah angka 5000 menjadi teks "Rp5.000"
function formatRupiah(angka) {
    return 'Rp' + Number(angka).toLocaleString('id-ID');
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="p-4">
            <!-- Kotak kartu: 1 kolom di layar kecil, 2 kolom di layar
                 sedang, 5 kolom di layar besar. gap-4 = jarak antar kartu. -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

                <!-- KARTU 1: Judul Buku. -->
                <div
                    class="rounded-lg border-l-4 border-blue-500 bg-white p-5
                           shadow-sm"
                >
                    <div class="text-sm text-gray-500">Judul Buku</div>
                    <div class="text-3xl font-bold text-gray-800">
                        {{ statistik.totalBuku }}
                    </div>
                    <div class="text-xs text-gray-500">
                        Total stok: {{ statistik.totalStok }} eksemplar
                    </div>
                </div>

                <!-- KARTU 2: Anggota. -->
                <div
                    class="rounded-lg border-l-4 border-green-500 bg-white p-5
                           shadow-sm"
                >
                    <div class="text-sm text-gray-500">Anggota</div>
                    <div class="text-3xl font-bold text-gray-800">
                        {{ statistik.totalAnggota }}
                    </div>
                    <div class="text-xs text-gray-500">
                        Terdaftar di perpustakaan
                    </div>
                </div>

                <!-- KARTU 3: Sedang Dipinjam -->
                <div
                    class="rounded-lg border-l-4 border-yellow-500 bg-white p-5
                           shadow-sm"
                >
                    <div class="text-sm text-gray-500">Sedang Dipinjam</div>
                    <div class="text-3xl font-bold text-gray-800">
                        {{ statistik.peminjamanAktif }}
                    </div>
                    <div
                        class="text-xs"
                        :class="statistik.peminjamanTerlambat > 0
                            ? 'text-red-600'
                            : 'text-gray-500'"
                    >
                        {{ statistik.peminjamanTerlambat }} terlambat
                    </div>
                </div>

                <!-- KARTU 4: Total Denda -->
                <div
                    class="rounded-lg border-l-4 border-red-500 bg-white p-5
                           shadow-sm"
                >
                    <div class="text-sm text-gray-500">Total Denda</div>
                    <div class="text-3xl font-bold text-gray-800">
                        {{ formatRupiah(statistik.totalDenda) }}
                    </div>
                    <div class="text-xs text-gray-500">
                        Dari buku yang sudah dikembalikan
                    </div>
                </div>

                <!-- KARTU 5: Peminjaman Hari Ini -->
                <div
                    class="rounded-lg border-l-4 border-purple-500 bg-white p-5
                           shadow-sm"
                >
                    <div class="text-sm text-gray-500">
                        Peminjaman Hari Ini
                    </div>
                    <div class="text-3xl font-bold text-gray-800">
                        {{ statistik.peminjamanHariIni }}
                    </div>
                    <div class="text-xs text-gray-500">
                        Total peminjaman hari ini
                    </div>
                </div>
            </div>

            <!-- Kotak grafik. mt-4 = jarak dari kartu di atasnya. -->
            <div class="mt-4 rounded-lg bg-white p-5 shadow-sm">
                <h3 class="mb-3 font-semibold text-gray-800">
                    Peminjaman 12 Bulan Terakhir
                </h3>
                <!-- Mengirim label dan jumlah ke BarChart sebagai props. -->
                <BarChart
                    :label="grafik.labels"
                    :jumlah="grafik.dataPeminjaman"
                    judul="Jumlah peminjaman"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
