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
        <!-- Perbaikan tampilan: seluruh isi dibungkus div p-4 (jarak dalam) seperti halaman Anggota -->
        <div class="p-4">
            <h1 class="text-xl font-bold">Data Buku</h1>

            <!-- Flash message: Menampilkan pesan sukses dari session jika ada -->
            <div v-if="$page.props.flash.success" class="my-2 text-green-700">
                {{ $page.props.flash.success }}
            </div>

            <div class="my-3 flex items-center gap-3">
                <!-- Link menuju form tambah buku, akan diarahkan ke Books/Create.vue -->
                <Link href="/books/create">+ Tambah Buku</Link>

                <!-- Tidak ada lagi @keyup.enter: pencarian berjalan otomatis lewat watch(keyword) -->
                <input
                    v-model="keyword"
                    type="text"
                    placeholder="Ketik judul atau pengarang..."
                >

                <!-- Penanda bahwa permintaan AJAX sedang berjalan -->
                <span v-if="sedangMencari" class="text-gray-500">Mencari...</span>
            </div>

            <!-- Pesan jika permintaan AJAX gagal -->
            <div v-if="pesanError" class="my-2 text-red-600">
                {{ pesanError }}
            </div>

            <!-- border-collapse: garis antar sel menyatu; border px-3 py-2: garis dan jarak isi -->
            <table class="border-collapse">
                <thead>
                    <tr>
                        <th class="border px-3 py-2">Kode</th>
                        <th class="border px-3 py-2">Judul</th>
                        <th class="border px-3 py-2">Pengarang</th>
                        <th class="border px-3 py-2">Stok</th>
                        <th class="border px-3 py-2">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <!-- v-for membaca daftarBuku (hasil pencarian atau daftar bawaan), bukan books.data -->
                    <tr v-for="book in daftarBuku" :key="book.id">
                        <td class="border px-3 py-2">{{ book.kode_buku }}</td>
                        <td class="border px-3 py-2">{{ book.judul }}</td>
                        <td class="border px-3 py-2">{{ book.pengarang }}</td>
                        <td class="border px-3 py-2">{{ book.stok }}</td>
                        <td class="border px-3 py-2">
                            <!-- Link menuju form edit buku, akan diarahkan ke Books/Edit.vue -->
                            <Link :href="`/books/${book.id}/edit`" class="mr-3">Edit</Link>

                            <!-- Tombol hapus buku, memanggil function hapus() di atas -->
                            <button @click="hapus(book.id)">Hapus</button>
                        </td>
                    </tr>

                    <!-- Pesan jika tidak ada data yang cocok -->
                    <tr v-if="daftarBuku.length === 0">
                        <td colspan="5" class="border px-3 py-2">
                            Tidak ada data yang ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Baris baru: tautan paginasi, hanya untuk daftar bawaan (sama seperti halaman Anggota) -->
            <div v-if="hasilCari === null" class="mt-3 flex gap-3">
                <template v-for="link in books.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" v-html="link.label" />
                    <span v-else v-html="link.label" class="text-gray-400" />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
