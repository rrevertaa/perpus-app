<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';

// useForm membuat sebuah objek form yang otomatis mengelola nilai input dan pesan error
const form = useForm({
    kode_buku: '',
    judul: '',
    pengarang: '',
    penerbit: '',
    tahun: '',
    stok: 0,
});

// Dipanggil kembali saat tombol submit diklik
function submit() {
    // Mengirim data form ke route books.store, yaitu method store() pada BookController
    form.post(route('books.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold">Tambah Buku</h1>

            <!-- @submit.prevent mencegah form reload halaman, lalu menjalankan submit() -->
            <!-- space-y-3: jarak antar kelompok isian; setiap isian dibungkus div -->
            <form @submit.prevent="submit" class="mt-3 space-y-3">
                <div>
                    <!-- label class="block": label tampil sebagai baris sendiri, isian turun ke bawah -->
                    <label class="block">Kode Buku</label>
                    <input v-model="form.kode_buku" type="text">
                    <!-- Menampilkan pesan error dari validasi Laravel jika ada -->
                    <div v-if="form.errors.kode_buku" class="text-red-600">
                        {{ form.errors.kode_buku }}
                    </div>
                </div>

                <div>
                    <label class="block">Judul</label>
                    <input v-model="form.judul" type="text">
                    <div v-if="form.errors.judul" class="text-red-600">
                        {{ form.errors.judul }}
                    </div>
                </div>

                <div>
                    <label class="block">Pengarang</label>
                    <input v-model="form.pengarang" type="text">
                    <div v-if="form.errors.pengarang" class="text-red-600">
                        {{ form.errors.pengarang }}
                    </div>
                </div>

                <div>
                    <label class="block">Penerbit</label>
                    <input v-model="form.penerbit" type="text">
                    <div v-if="form.errors.penerbit" class="text-red-600">
                        {{ form.errors.penerbit }}
                    </div>
                </div>

                <div>
                    <label class="block">Tahun</label>
                    <input v-model="form.tahun" type="text">
                    <div v-if="form.errors.tahun" class="text-red-600">
                        {{ form.errors.tahun }}
                    </div>
                </div>

                <div>
                    <label class="block">Stok</label>
                    <input v-model="form.stok" type="number">
                    <div v-if="form.errors.stok" class="text-red-600">
                        {{ form.errors.stok }}
                    </div>
                </div>

                <button type="submit" :disabled="form.processing">Simpan</button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
