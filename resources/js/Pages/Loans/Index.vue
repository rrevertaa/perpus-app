<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

// 'loans' diterima dari LoanController@index, berisi data peminjaman
// beserta anggota (loan.member) dan buku (loan.book) yang terkait
defineProps({
    loans: Object,
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="p-4">
            <h1 class="text-xl font-bold">Data Peminjaman</h1>

            <!-- Flash message: pesan sukses dari session -->
            <div v-if="$page.props.flash.success" class="my-2 text-green-700">
                {{ $page.props.flash.success }}
            </div>

            <div class="my-3">
                <!-- Link menuju form peminjaman -->
                <Link href="/loans/create">+ Pinjam Buku</Link>
            </div>

            <table class="border-collapse">
                <thead>
                    <tr>
                        <th class="border px-3 py-2">Anggota</th>
                        <th class="border px-3 py-2">Kode Buku</th>
                        <th class="border px-3 py-2">Buku</th>
                        <th class="border px-3 py-2">Tanggal Pinjam</th>
                        <th class="border px-3 py-2">Jatuh Tempo</th>
                        <th class="border px-3 py-2">Status</th>
                    </tr>
                </thead>

                <tbody>
                    <!-- loans.data berisi daftar peminjaman untuk halaman ini -->
                    <tr v-for="loan in loans.data" :key="loan.id">
                        <td class="border px-3 py-2">
                            {{ loan.member.nama }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ loan.book.kode_buku }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ loan.book.judul }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ loan.tanggal_pinjam }}
                        </td>

                        <td class="border px-3 py-2">
                            {{ loan.tanggal_kembali_rencana }}
                        </td>

                        <td class="border px-3 py-2">
                            <!-- Warna status: kuning jika masih dipinjam, hijau jika sudah kembali -->
                            <span
                                :class="loan.status === 'dipinjam'
                                    ? 'text-yellow-700'
                                    : 'text-green-700'"
                            >
                                {{ loan.status }}
                            </span>
                        </td>
                    </tr>

                    <!-- Pesan jika belum ada data peminjaman -->
                    <tr v-if="loans.data.length === 0">
                        <td colspan="6" class="border px-3 py-2">
                            Belum ada data peminjaman.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Tautan paginasi -->
            <div class="mt-3 flex gap-3">
                <template v-for="link in loans.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="text-gray-400"
                    />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
