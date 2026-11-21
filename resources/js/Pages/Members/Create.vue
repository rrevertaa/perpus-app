<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    nama: '',
    nis_nim: '',
    no_hp: '',
    foto: null, // berisi berkas (File) yang dipilih, bukan teks
});

// Alamat sementara gambar di browser, hanya untuk pratinjau sebelum disimpan
const pratinjau = ref(null);

// Dipanggil saat pengguna memilih berkas pada <input type="file">
function pilihFoto(event) {
    const berkas = event.target.files[0];
    form.foto = berkas ?? null;
    pratinjau.value = berkas ? URL.createObjectURL(berkas) : null;
}

function submit() {
    // Karena form.foto berisi berkas, Inertia otomatis mengirim sebagai multipart/form-data
    form.post(route('members.store'));
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold">Tambah Anggota</h1>

            <form @submit.prevent="submit" class="mt-3 space-y-3">
                <div>
                    <label class="block">Nama</label>
                    <input v-model="form.nama" type="text">
                    <div v-if="form.errors.nama" class="text-red-600">
                        {{ form.errors.nama }}
                    </div>
                </div>

                <div>
                    <label class="block">NIS/NIM</label>
                    <input v-model="form.nis_nim" type="text">
                    <div v-if="form.errors.nis_nim" class="text-red-600">
                        {{ form.errors.nis_nim }}
                    </div>
                </div>

                <div>
                    <label class="block">No. HP</label>
                    <input v-model="form.no_hp" type="text">
                    <div v-if="form.errors.no_hp" class="text-red-600">
                        {{ form.errors.no_hp }}
                    </div>
                </div>

                <div>
                    <label class="block">Foto (JPG/PNG, maksimal 2 MB)</label>

                    <!-- Input berkas tidak bisa memakai v-model, sehingga memakai @change -->
                    <input
                        type="file"
                        accept="image/png, image/jpeg"
                        @change="pilihFoto"
                    >

                    <div v-if="form.errors.foto" class="text-red-600">
                        {{ form.errors.foto }}
                    </div>

                    <img
                        v-if="pratinjau"
                        :src="pratinjau"
                        alt="Pratinjau foto"
                        class="mt-2 h-32 w-32 object-cover"
                    >
                </div>

                <button type="submit" :disabled="form.processing">
                    Simpan
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
