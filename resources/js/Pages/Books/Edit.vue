<script setup> 
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; 
import { useForm } from '@inertiajs/vue3'; 
  
// 'book' diterima dari BookController@edit (Langkah 2), berisi data lama buku ini 
const props = defineProps({ 
    book: Object, 
}); 
  
// Berbeda dari Create.vue: nilai awal form diisi dari props.book, bukan string/angka kosong 
const form = useForm({ 
    kode_buku: props.book.kode_buku, 
    judul: props.book.judul, 
    pengarang: props.book.pengarang, 
    penerbit: props.book.penerbit, 
    tahun: props.book.tahun, 
    stok: props.book.stok, 
}); 
  
function submit() { 
    // form.put() (bukan form.post() seperti pada Create.vue) karena ini operasi Update 
    // route('books.update', props.book.id) merujuk ke method update() pada Langkah 2 
    form.put(route('books.update', props.book.id)); 
} 
</script> 
  
<template> 
    <AuthenticatedLayout> 
        <h1>Edit Buku</h1> 
  
        <form @submit.prevent="submit"> 
            <label>Kode Buku</label> 
            <input v-model="form.kode_buku" type="text"> 
            <span v-if="form.errors.kode_buku">{{ form.errors.kode_buku }}</span> 
  
            <label>Judul</label> 
            <input v-model="form.judul" type="text"> 
  
            <label>Pengarang</label> 
            <input v-model="form.pengarang" type="text"> 
  
            <label>Penerbit</label> 
            <input v-model="form.penerbit" type="text"> 
  
            <label>Tahun</label> 
            <input v-model="form.tahun" type="text"> 
  
            <label>Stok</label> 
<input v-model="form.stok" type="number"> 
<button type="submit">Simpan Perubahan</button> 
</form> 
</AuthenticatedLayout> 
</template> 