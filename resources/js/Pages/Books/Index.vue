<script setup>
// Mengambil layout dashboard yang sudah dibuat pada Pertemuan 5 (lengkap dengan Sidebar)
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';

const props = defineProps({
    books: Object,
    search: String,
});

// Isi kotak pencarian
const keyword = ref(props.search ?? '');

// Hasil pencarian dari server
const hasilCari = ref(null);
const sedangMencari = ref(false);
const pesanError = ref('');

// Timer debounce dan pembatal permintaan Axios
let timer = null;
let permintaan = null;

// Data yang ditampilkan di tabel
const daftarBuku = computed(() => hasilCari.value ?? props.books.data);

// Mengirim permintaan AJAX ke /books/search lalu menyimpan hasilnya
async function ambilHasil() {
    // Batalkan permintaan sebelumnya yang belum selesai
    if (permintaan) {
        permintaan.abort();
    }

    permintaan = new AbortController();

    sedangMencari.value = true;
    pesanError.value = '';

    try {
        const respons = await axios.get(route('books.search'), {
            params: { q: keyword.value.trim() },
            signal: permintaan.signal,
        });

        hasilCari.value = respons.data;
        sedangMencari.value = false;
    } catch (error) {
        // Permintaan yang sengaja dibatalkan bukan kesalahan
        if (axios.isCancel(error)) {
            return;
        }

        sedangMencari.value = false;
        pesanError.value = 'Pencarian gagal. Muat ulang halaman, lalu coba lagi.';
    }
}

// watch dijalankan setiap kali isi kotak pencarian berubah
watch(keyword, (nilai) => {
    // Hentikan hitungan mundur lama
    clearTimeout(timer);

    // Kotak dikosongkan: batalkan permintaan, kembali ke daftar bawaan
    if (nilai.trim() === '') {
        if (permintaan) {
            permintaan.abort();
        }

        hasilCari.value = null;
        sedangMencari.value = false;
        pesanError.value = '';
        return;
    }

    // Mulai hitungan mundur 300 milidetik
    timer = setTimeout(ambilHasil, 800);
});

// Function dari Pertemuan 7, ditambah onSuccess
function hapus(id) {
    if (confirm('Yakin ingin menghapus buku ini?')) {
        router.delete(route('books.destroy', id), {
            onSuccess: () => {
                if (keyword.value.trim() !== '') {
                    ambilHasil();
                }
            },
        });
    }
}

// Saat halaman ditinggalkan, hentikan timer dan permintaan
onBeforeUnmount(() => {
    clearTimeout(timer);

    if (permintaan) {
        permintaan.abort();
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <h1>Data Buku</h1>

        <!-- Flash message: Menampilkan pesan sukses dari session jika ada -->
        <div
            v-if="$page.props.flash.success"
            class="alert alert-success"
            style="color: red; margin-bottom: 10px;"
        >
            {{ $page.props.flash.success }}
        </div>

        <!-- Link menuju form tambah buku -->
        <Link href="/books/create">+ Tambah Buku</Link>

        <!-- Pencarian otomatis melalui watch(keyword) -->
        <input
            v-model="keyword"
            type="text"
            placeholder="Ketik judul, pengarang, atau penerbit..."
        >

        <!-- Penanda bahwa permintaan AJAX sedang berjalan -->
        <span v-if="sedangMencari">Mencari...</span>

        <!-- Pesan jika permintaan AJAX gagal -->
        <p v-if="pesanError">{{ pesanError }}</p>

        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Judul</th>
                    <th>Pengarang</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <!-- Membaca hasil pencarian atau daftar bawaan -->
                <tr v-for="book in daftarBuku" :key="book.id">
                    <td>{{ book.kode_buku }}</td>
                    <td>{{ book.judul }}</td>
                    <td>{{ book.pengarang }}</td>
                    <td>{{ book.stok }}</td>
                    <td>
                        <!-- Link menuju form edit buku -->
                        <Link :href="`/books/${book.id}/edit`">Edit</Link>

                        <!-- Tombol hapus buku -->
                        <button @click="hapus(book.id)">Hapus</button>
                    </td>
                </tr>

                <!-- Pesan jika tidak ada data yang cocok -->
                <tr v-if="daftarBuku.length === 0">
                    <td colspan="5">Tidak ada buku yang ditemukan.</td>
                </tr>
            </tbody>
        </table>
    </AuthenticatedLayout>
</template>
