<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    members: Object,
    search: String,
});

// Nilai awal kotak pencarian diisi dari props.search
const keyword = ref(props.search);

function cari() {
    router.get('/members', { search: keyword.value });
}

function hapus(id) {
    if (confirm('Yakin ingin menghapus anggota ini?')) {
        router.delete(route('members.destroy', id));
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold">Data Anggota</h1>

            <div v-if="$page.props.flash.success" class="my-2 text-green-700">
                {{ $page.props.flash.success }}
            </div>

            <div class="my-3 flex items-center gap-3">
                <Link href="/members/create">+ Tambah Anggota</Link>

                <input
                    v-model="keyword"
                    @keyup.enter="cari"
                    type="text"
                    placeholder="Cari nama, NIS/NIM, atau No. HP..."
                >
            </div>

            <table class="border-collapse">
                <thead>
                    <tr>
                        <th class="border px-3 py-2">Foto</th>
                        <th class="border px-3 py-2">NIS/NIM</th>
                        <th class="border px-3 py-2">Nama</th>
                        <th class="border px-3 py-2">No. HP</th>
                        <th class="border px-3 py-2">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="member in members.data" :key="member.id">
                        <td class="border px-3 py-2">
                            <img
                                v-if="member.foto"
                                :src="`/storage/${member.foto}`"
                                alt="Foto anggota"
                                class="h-16 w-16 object-cover"
                            >
                            <span v-else>-</span>
                        </td>

                        <td class="border px-3 py-2">{{ member.nis_nim }}</td>
                        <td class="border px-3 py-2">{{ member.nama }}</td>
                        <td class="border px-3 py-2">{{ member.no_hp }}</td>

                        <td class="border px-3 py-2">
                            <Link :href="`/members/${member.id}/edit`" class="mr-3">Edit</Link>
                            <button @click="hapus(member.id)">Hapus</button>
                        </td>
                    </tr>

                    <tr v-if="members.data.length === 0">
                        <td colspan="5" class="border px-3 py-2">
                            Belum ada data anggota.
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-3 flex gap-3">
                <template v-for="link in members.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" v-html="link.label" />
                    <span v-else v-html="link.label" class="text-gray-400" />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
