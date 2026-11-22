<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

// 'book' diterima dari BookController@edit, berisi data lama buku ini
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
    // route('books.update', props.book.id) merujuk ke method update() pada BookController
    form.put(route('books.update', props.book.id));
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold">Edit Buku</h1>

            <form @submit.prevent="submit" class="mt-3 space-y-3">
                <div>
                    <label class="block">Kode Buku</label>
                    <input v-model="form.kode_buku" type="text">
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

                <button type="submit" :disabled="form.processing">Simpan Perubahan</button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
