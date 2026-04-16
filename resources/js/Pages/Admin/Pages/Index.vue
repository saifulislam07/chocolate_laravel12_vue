<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    pages: Array
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '60px' },
    { key: 'title', label: 'Title', sortable: true },
    { key: 'slug', label: 'Slug / URL', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' }
];

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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Content Pages</h1>
                        <p class="text-muted text-sm mb-0">Manage static pages like Privacy Policy, About Us, etc.</p>
                    </div>
                    <Link :href="route('admin.pages.create')" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus mr-2"></i> Add New Page
                    </Link>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <PremiumTable 
                    :items="pages" 
                    :headers="columns"
                    search-placeholder="Search pages by title or slug..."
                >
                    <!-- Index Cell -->
                    <template #cell-index="{ index }">
                        <span class="text-muted">{{ index + 1 }}</span>
                    </template>

                    <!-- Title Cell -->
                    <template #cell-title="{ item }">
                        <div class="font-weight-bold text-dark">{{ item.title }}</div>
                    </template>

                    <!-- Slug Cell -->
                    <template #cell-slug="{ item }">
                        <code class="text-xs px-2 py-1 bg-light rounded text-indigo">/{{ item.slug }}</code>
                    </template>

                    <!-- Status Cell -->
                    <template #cell-is_active="{ item }">
                        <span class="badge" :class="item.is_active ? 'badge-success' : 'badge-danger'">
                            {{ item.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </template>

                    <!-- Actions Cell -->
                    <template #cell-actions="{ item }">
                        <div class="d-flex">
                            <Link :href="route('admin.pages.edit', item.id)" class="btn btn-light btn-sm mr-2 border shadow-none" title="Edit">
                                <i class="fas fa-edit text-primary text-xs"></i>
                            </Link>
                            <button @click="deletePage(item.id)" class="btn btn-light btn-sm border shadow-none" title="Delete">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>
    </AdminLayout>
</template>
