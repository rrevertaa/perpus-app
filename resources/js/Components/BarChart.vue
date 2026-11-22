<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
// Chart.js: pustaka JavaScript untuk menggambar grafik pada <canvas>.
// 'chart.js/auto' langsung mendaftarkan semua jenis grafik.
import Chart from 'chart.js/auto';

// Data yang diterima dari komponen induk (Dashboard.vue)
const props = defineProps({
    // Teks sumbu X, contoh: ['Jun 2026', 'Jul 2026', ...]
    label: Array,
    // Angka tinggi batang, contoh: [3, 5, 2, ...]
    jumlah: Array,
    // Nama data, tampil saat kursor diarahkan ke batang
    judul: { type: String, default: 'Jumlah' },
});

// Penghubung ke elemen <canvas> (diisi Vue setelah halaman tampil)
const kanvas = ref(null);

// Menyimpan objek grafik agar bisa dihapus ketika komponen dilepas
let grafik = null;

// onMounted: dijalankan SETELAH elemen <canvas> benar-benar ada di halaman
onMounted(() => {
    // new Chart(elemen, pengaturan) menggambar grafik pada elemen itu
    grafik = new Chart(kanvas.value, {
        // Jenis grafik: 'bar' = batang. Ganti 'line' untuk grafik garis.
        type: 'bar',
        data: {
            // [...props.label] menyalin array supaya Chart.js tidak
            // berhadapan langsung dengan objek reaktif milik Vue
            labels: [...props.label],
            datasets: [
                {
                    label: props.judul,
                    data: [...props.jumlah],
                    // Warna batang (biru, kode warna heksadesimal)
                    backgroundColor: '#3b82f6',
                },
            ],
        },
        options: {
            // Ukuran grafik mengikuti ukuran <div> pembungkusnya
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                // Sembunyikan legenda karena hanya ada satu jenis data
                legend: { display: false },
            },
            scales: {
                y: {
                    // Sumbu Y mulai dari 0
                    beginAtZero: true,
                    // precision: 0 = angka bulat saja (tanpa 0.5 peminjaman)
                    ticks: { precision: 0 },
                },
            },
        },
    });
});

// onBeforeUnmount: dijalankan sebelum komponen dilepas (misalnya pindah
// halaman). destroy() melepas grafik dari <canvas> agar tidak error.
onBeforeUnmount(() => {
    if (grafik) {
        grafik.destroy();
    }
});
</script>

<template>
    <!-- Tinggi 16rem (h-64). Chart.js menyesuaikan ukuran grafik dengan
         div ini. -->
    <div class="h-64">
        <!-- ref="kanvas" menghubungkan elemen ini dengan variabel kanvas -->
        <canvas ref="kanvas"></canvas>
    </div>
</template>
