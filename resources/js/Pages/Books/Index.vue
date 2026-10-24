<script setup> 
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; 
import { Link, router } from '@inertiajs/vue3'; 
import { ref } from 'vue'; 

const props = defineProps({ 
    books: Object, 
    search: String,   
})
// Nilai awal kotak pencarian diisi dari props.search, bukan selalu kosong 
const keyword = ref(props.search); 
  
// Function baru: dijalankan saat pengguna menekan Enter pada kotak pencarian 
function cari() { 
    router.get('/books', { search: keyword.value }); 
} 
  
function hapus(id) { 
    if (confirm('Yakin ingin menghapus buku ini?')) { 
        router.delete(route('books.destroy', id)); 
    } 
} 
</script>
  
<template> 
    <AuthenticatedLayout> 
        <h1>Data Buku</h1> 
  
        <!-- Flash message baru: $page.props.flash tersedia karena Langkah 5 --> 
        <div v-if="$page.props.flash?.success">
            {{ $page.props.flash.success }}
        </div> 
        <div
            v-if="$page.props.flash?.error"
            class="mb-4 rounded bg-red-100 p-3 text-red-700"
            >
            {{ $page.props.flash.error }}
        </div>
  
        <Link href="/books/create">+ Tambah Buku</Link> 

        <!-- @keyup.enter memanggil function cari() hanya saat tombol Enter ditekan --> 
            <input 
                v-model="keyword" 
                @keyup.enter="cari" 
                type="text" 
            placeholder="Cari judul atau pengarang..." 
            > 
  
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Tahun</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="book in books.data" :key="book.id">
                        <td>{{ book.kode_buku }}</td>
                        <td>{{ book.judul }}</td>
                        <td>{{ book.pengarang }}</td>
                        <td>{{ book.tahun || '-' }}</td>
                        <td>{{ book.stok }}</td>
                        <td>
                            <Link :href="`/books/${book.id}/edit`">
                                Edit
                            </Link>

                            <button @click="hapus(book.id)">
                                Hapus
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

    </AuthenticatedLayout> 
</template>