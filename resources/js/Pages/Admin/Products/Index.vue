<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    products: Array
});

function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('admin.products.destroy', id));
    }
}

const getPrimaryImage = (images) => {
    if (!images || images.length === 0) return 'https://via.placeholder.com/50';
    const primary = images.find(img => img.is_primary);
    return primary ? primary.image_path : images[0].image_path;
};
</script>

<template>
    <Head title="Products" />
    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Product Management</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title">All Products</h3>
                        <div class="card-tools">
                            <Link :href="route('admin.products.create')" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Add New Product
                            </Link>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-valign-middle table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category/Brand</th>
                                    <th>Price/Cost</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th style="width: 150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in products" :key="product.id">
                                    <td>
                                        <img :src="getPrimaryImage(product.images)" alt="Product Image" class="img-circle img-size-32 mr-2" style="object-fit: cover;">
                                        <span class="font-weight-bold">{{ product.name }}</span>
                                        <br>
                                        <small class="text-muted ml-5 pl-1">SKU: {{ product.sku || 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info mr-1" v-if="product.category">{{ product.category.name }}</span>
                                        <span class="badge badge-secondary" v-if="product.brand">{{ product.brand.name }}</span>
                                    </td>
                                    <td>
                                        <span class="text-success font-weight-bold">৳{{ product.price }}</span><br>
                                        <small class="text-muted">Cost: ৳{{ product.cost_price }}</small>
                                    </td>
                                    <td>
                                        <span class="badge" :class="product.stock <= product.alert_quantity ? 'badge-danger' : 'badge-success'">
                                            {{ product.stock }} {{ product.unit?.short_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="product.is_active ? 'badge-success' : 'badge-danger'">
                                            {{ product.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <Link :href="route('admin.products.edit', product.id)" class="btn btn-info btn-xs mr-1">
                                            <i class="fas fa-edit"></i>
                                        </Link>
                                        <button class="btn btn-danger btn-xs" @click="deleteProduct(product.id)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="products.length === 0">
                                    <td colspan="6" class="text-center p-4 text-muted">No products found. Start by adding a new product.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
