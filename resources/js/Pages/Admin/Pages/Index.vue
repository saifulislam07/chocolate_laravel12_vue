<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    pages: Array
});

const deletePage = (id) => {
    if (confirm('Are you sure you want to delete this page?')) {
        router.delete(route('admin.pages.destroy', id));
    }
};
</script>

<template>
    <Head title="Content Pages" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-file-alt mr-2 text-primary"></i>Content Pages</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary card-outline shadow-sm" style="border-radius: 12px;">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">Page List</h3>
                        <div class="card-tools">
                            <Link :href="route('admin.pages.create')" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                <i class="fas fa-plus mr-1"></i> Add New Page
                            </Link>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Title</th>
                                    <th>Slug</th>
                                    <th class="text-center">Status</th>
                                    <th style="width: 150px" class="text-right pr-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(page, index) in pages" :key="page.id">
                                    <td>{{ index + 1 }}</td>
                                    <td class="font-weight-bold">{{ page.title }}</td>
                                    <td><code>/{{ page.slug }}</code></td>
                                    <td class="text-center">
                                        <span class="badge" :class="page.is_active ? 'badge-success' : 'badge-danger'">
                                            {{ page.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-right pr-4">
                                        <Link :href="route('admin.pages.edit', page.id)" class="btn btn-outline-info btn-xs mr-1 p-1 px-2">
                                            <i class="fas fa-edit"></i>
                                        </Link>
                                        <button class="btn btn-outline-danger btn-xs p-1 px-2" @click="deletePage(page.id)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="pages.length === 0">
                                    <td colspan="5" class="text-center p-5 text-muted h5">No pages found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
