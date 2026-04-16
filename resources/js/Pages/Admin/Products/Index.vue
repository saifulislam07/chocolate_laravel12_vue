<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    products: Array
});

const columns = [
    { key: 'name', label: 'Product', sortable: true },
    { key: 'category', label: 'Category/Brand', sortable: true },
    { key: 'price', label: 'Price/Cost', sortable: true },
    { key: 'stock', label: 'Stock', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' }
];

function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('admin.products.destroy', id));
    }
}

const getPrimaryImage = (images) => {
    if (!images || images.length === 0) return 'https://ui-avatars.com/api/?background=f1f5f9&color=64748b&name=P';
    const primary = images.find(img => img.is_primary);
    return primary ? primary.image_path : images[0].image_path;
};
</script>

<template>
    <Head title="Products" />
    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Product Catalog</h1>
                        <p class="text-muted text-sm mb-0">Manage and track your inventory products</p>
                    </div>
                    <Link :href="route('admin.products.create')" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus mr-2"></i> Add New Product
                    </Link>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <PremiumTable 
                    :items="products" 
                    :headers="columns"
                    search-placeholder="Search by name, SKU, or category..."
                >
                    <!-- Product Cell -->
                    <template #cell-name="{ item }">
                        <div class="d-flex align-items-center">
                            <div class="product-img-wrapper mr-3">
                                <img :src="getPrimaryImage(item.images)" class="rounded-lg shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                            </div>
                            <div>
                                <div class="font-weight-bold text-dark">{{ item.name }}</div>
                                <div class="text-xs text-muted">SKU: {{ item.sku || 'N/A' }}</div>
                            </div>
                        </div>
                    </template>

                    <!-- Category/Brand Cell -->
                    <template #cell-category="{ item }">
                        <div class="d-flex flex-column gap-1">
                            <span class="badge badge-info mb-1" v-if="item.category">{{ item.category.name }}</span>
                            <span class="badge badge-secondary" v-if="item.brand">{{ item.brand.name }}</span>
                        </div>
                    </template>

                    <!-- Price Cell -->
                    <template #cell-price="{ item }">
                        <div class="font-weight-bold text-dark">৳{{ item.price }}</div>
                        <div class="text-xs text-muted">Cost: ৳{{ item.cost_price }}</div>
                    </template>

                    <!-- Stock Cell -->
                    <template #cell-stock="{ item }">
                        <span class="badge" :class="item.stock <= item.alert_quantity ? 'badge-danger' : 'badge-success'">
                            <i class="fas fa-cubes mr-1 text-xs opacity-50"></i>
                            {{ item.stock }} {{ item.unit?.short_name || 'Units' }}
                        </span>
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
                            <Link :href="route('admin.products.edit', item.id)" class="btn btn-light btn-sm mr-2 border shadow-none" title="Edit">
                                <i class="fas fa-pen text-primary text-xs"></i>
                            </Link>
                            <button @click="deleteProduct(item.id)" class="btn btn-light btn-sm border shadow-none" title="Delete">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.product-img-wrapper {
    flex-shrink: 0;
}
.gap-1 {
    gap: 0.25rem;
}
</style>
