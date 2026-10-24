<script setup> 
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; 
import { useForm } from '@inertiajs/vue3'; 
// useForm membuat sebuah objek form yang otomatis mengelola nilai input dan pesan error 
    const form = useForm({ 
    kode_buku: '', 
    judul: '', 
    pengarang: '', 
    penerbit: '', 
    tahun: '', 
    stok: 0, 
}); 
  
// Dipanggil ketika tombol submit diklik 
function submit() { 
    // Mengirim data form ke route books.store, yaitu method store() pada BookController (Langkah 5) 
    form.post(route('books.store')); 
} 
</script> 
  
<template> 
    <AuthenticatedLayout> 
        <h1>Tambah Buku</h1> 
  
        <form @submit.prevent="submit"> 
            <!-- @submit.prevent mencegah form reload halaman, lalu menjalankan function 
submit() --> 
  
            <label>Kode Buku</label> 
            <input v-model="form.kode_buku" type="text"> 
            <!-- Menampilkan pesan error dari validasi Laravel jika ada --> 
            <span v-if="form.errors.kode_buku">{{ form.errors.kode_buku }}</span> 
  
            <label>Judul</label> 
            <input v-model="form.judul" type="text"> 
            <span v-if="form.errors.judul">{{ form.errors.judul }}</span> 
  
            <label>Pengarang</label> 
            <input v-model="form.pengarang" type="text"> 
            <span v-if="form.errors.pengarang">{{ form.errors.pengarang }}</span> 
  
            <label>Penerbit</label> 
            <input v-model="form.penerbit" type="text"> 
  
            <label>Tahun</label> 
            <input v-model="form.tahun" type="text"> 
  
            <label>Stok</label> 
            <input v-model="form.stok" type="number"> 
  
            <button type="submit">Simpan</button> 
        </form> 
    </AuthenticatedLayout> 
</template> 