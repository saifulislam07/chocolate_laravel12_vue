<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    bundles: { type: Array, default: () => [] },
});

const columns = [
    { key: 'name', label: 'Bundle', sortable: true },
    { key: 'items', label: 'Items', sortable: false },
    { key: 'price', label: 'Price', sortable: true },
    { key: 'stock', label: 'Stock', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' },
];

function getPrimaryImage(images) {
    if (!images || images.length === 0) return 'https://ui-avatars.com/api/?background=f1f5f9&color=64748b&name=B';
    const primary = images.find((image) => image.is_primary);
    return primary ? primary.image_path : images[0].image_path;
}

function formatMoney(value) {
    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 0,
    }).format(Number(value || 0));
}

function deleteBundle(id) {
    if (confirm('Are you sure you want to delete this bundle?')) {
        router.delete(route('admin.bundles.destroy', id));
    }
}
</script>

<template>
    <Head title="Bundles" />
    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Product Bundles</h1>
                        <p class="text-muted text-sm mb-0">Create curated product sets with notes and discounts</p>
                    </div>
                    <Link :href="route('admin.bundles.create')" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus mr-2"></i> Add Bundle
                    </Link>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <PremiumTable :items="bundles" :headers="columns" search-placeholder="Search bundle name, SKU, or note...">
                    <template #cell-name="{ item }">
                        <div class="d-flex align-items-center">
                            <img :src="getPrimaryImage(item.images)" class="rounded-lg shadow-sm mr-3" style="width: 44px; height: 44px; object-fit: cover;">
                            <div>
                                <div class="font-weight-bold text-dark">{{ item.name }}</div>
                                <div class="text-xs text-muted">SKU: {{ item.sku || 'N/A' }}</div>
                                <div v-if="item.bundle_note" class="text-xs text-muted">{{ item.bundle_note }}</div>
                            </div>
                        </div>
                    </template>

                    <template #cell-items="{ item }">
                        <div class="d-flex flex-column">
                            <span v-for="product in item.bundle_items" :key="product.id" class="text-sm">
                                {{ product.pivot.quantity }} x {{ product.name }}
                            </span>
                        </div>
                    </template>

                    <template #cell-price="{ item }">
                        <div class="font-weight-bold text-success">{{ formatMoney(item.price) }}</div>
                        <div v-if="Number(item.compare_at_price) > Number(item.price)" class="text-xs text-muted">
                            Was {{ formatMoney(item.compare_at_price) }}
                        </div>
                    </template>

                    <template #cell-stock="{ item }">
                        <span class="badge" :class="item.stock <= item.alert_quantity ? 'badge-danger' : 'badge-success'">
                            {{ item.stock }} sets
                        </span>
                    </template>

                    <template #cell-is_active="{ item }">
                        <span class="badge" :class="item.is_active ? 'badge-success' : 'badge-danger'">
                            {{ item.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </template>

                    <template #cell-actions="{ item }">
                        <div class="d-flex">
                            <Link :href="route('admin.bundles.edit', item.id)" class="btn btn-light btn-sm mr-2 border shadow-none" title="Edit">
                                <i class="fas fa-pen text-primary text-xs"></i>
                            </Link>
                            <button class="btn btn-light btn-sm border shadow-none" title="Delete" @click="deleteBundle(item.id)">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>
    </AdminLayout>
</template>
