<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';

const form = useForm({
    kode_buku: '',
    judul: '',
    pengarang: '',
    penerbit: '',
    tahun: '',
    stok: 0,
});

// Status pemeriksaan kode buku
const statusKode = ref('');
let timerKode = null;
let permintaanKode = null;

// Memeriksa apakah kode buku sudah digunakan
async function periksaKode() {
    if (permintaanKode) {
        permintaanKode.abort();
    }

    permintaanKode = new AbortController();
    statusKode.value = 'memeriksa';

    try {
        const respons = await axios.get(route('books.cek-kode'), {
            params: {
                kode: form.kode_buku.trim(),
            },
            signal: permintaanKode.signal,
        });

        statusKode.value = respons.data.tersedia
            ? 'tersedia'
            : 'terpakai';
    } catch (error) {
        if (axios.isCancel(error) || error.name === 'CanceledError') {
            return;
        }

        statusKode.value = 'gagal';
    }
}

// Memeriksa kode setelah pengguna berhenti mengetik selama 500 ms
watch(() => form.kode_buku, (nilai) => {
    clearTimeout(timerKode);

    if (permintaanKode) {
        permintaanKode.abort();
    }

    if (!nilai || nilai.trim() === '') {
        statusKode.value = '';
        return;
    }

    statusKode.value = 'memeriksa';
    timerKode = setTimeout(periksaKode, 500);
});

// Membersihkan timer dan permintaan ketika halaman ditinggalkan
onBeforeUnmount(() => {
    clearTimeout(timerKode);

    if (permintaanKode) {
        permintaanKode.abort();
    }
});

// Dipanggil ketika tombol submit diklik
function submit() {
    form.post(route('books.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <h1>Tambah Buku</h1>

        <form @submit.prevent="submit">

            <label>Kode Buku</label>
            <input v-model="form.kode_buku" type="text">

            <span v-if="statusKode === 'memeriksa'" class="text-gray-500">
                Memeriksa kode buku...
            </span>

            <span v-else-if="statusKode === 'tersedia'" class="text-green-700">
                Kode buku tersedia.
            </span>

            <span v-else-if="statusKode === 'terpakai'" class="text-red-600">
                Kode buku sudah digunakan. Gunakan kode lain.
            </span>

            <span v-else-if="statusKode === 'gagal'" class="text-yellow-700">
                Pemeriksaan otomatis gagal. Server akan memeriksa ulang saat disimpan.
            </span>

            <span v-if="form.errors.kode_buku" class="text-red-600">
                {{ form.errors.kode_buku }}
            </span>

            <label>Judul</label>
            <input v-model="form.judul" type="text">
            <span v-if="form.errors.judul">
                {{ form.errors.judul }}
            </span>

            <label>Pengarang</label>
            <input v-model="form.pengarang" type="text">
            <span v-if="form.errors.pengarang">
                {{ form.errors.pengarang }}
            </span>

            <label>Penerbit</label>
            <input v-model="form.penerbit" type="text">

            <label>Tahun</label>
            <input v-model="form.tahun" type="text">

            <label>Stok</label>
            <input v-model="form.stok" type="number">

            <button
                type="submit"
                :disabled="form.processing || statusKode === 'terpakai'"
            >
                Simpan
            </button>
        </form>
    </AuthenticatedLayout>
</template>
