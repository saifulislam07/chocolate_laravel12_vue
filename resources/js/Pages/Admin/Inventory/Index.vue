<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: { type: Array, default: () => [] },
    movements: { type: Array, default: () => [] },
});

const columns = [
    { key: 'name', label: 'Product', sortable: true },
    { key: 'sku', label: 'SKU', sortable: true },
    { key: 'stock', label: 'Current Stock', sortable: true, cellClass: 'text-center' },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' },
];

const movementColumns = [
    { key: 'created_at', label: 'Date', sortable: true },
    { key: 'product', label: 'Product', sortable: false },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'quantity', label: 'Change', sortable: false, cellClass: 'text-center' },
    { key: 'resulting_stock', label: 'Resulting Stock', sortable: false, cellClass: 'text-center' },
    { key: 'note', label: 'Note', sortable: false },
];

const showModal = ref(false);
const form = useForm({
    product_id: '',
    delta: '',
    note: '',
});

function openAdjustModal(product) {
    form.reset();
    form.product_id = product.id;
    showModal.value = true;
}

function submit() {
    form.post(route('admin.inventory.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
}

const typeBadges = {
    purchase_in: 'badge-success',
    sale_out: 'badge-info',
    return_in: 'badge-warning',
    adjustment: 'badge-secondary',
};
</script>

<template>
    <Head title="Inventory" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-4">
                    <h1 class="m-0 text-dark font-bold h3">Inventory Management</h1>
                    <p class="text-muted text-sm mb-0">Current stock levels, manual adjustments, and the full movement ledger.</p>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <h6 class="font-weight-bold text-uppercase text-xs text-muted mb-3">Product Stock</h6>
                <PremiumTable :items="products" :headers="columns" search-placeholder="Search products...">
                    <template #cell-name="{ item }">
                        <span class="font-weight-bold text-dark">{{ item.name }}</span>
                    </template>
                    <template #cell-sku="{ item }">{{ item.sku || 'N/A' }}</template>
                    <template #cell-stock="{ item }">
                        <span class="badge" :class="item.stock <= item.alert_quantity ? 'badge-danger' : 'badge-success'">
                            {{ item.stock }}
                        </span>
                    </template>
                    <template #cell-actions="{ item }">
                        <button @click="openAdjustModal(item)" class="btn btn-light btn-sm border shadow-none">
                            <i class="fas fa-pen text-primary text-xs mr-1"></i> Adjust
                        </button>
                    </template>
                </PremiumTable>

                <h6 class="font-weight-bold text-uppercase text-xs text-muted mt-5 mb-3">Recent Stock Movements</h6>
                <PremiumTable :items="movements" :headers="movementColumns" search-placeholder="Search movements...">
                    <template #cell-created_at="{ item }">
                        <span class="text-xs text-muted">{{ new Date(item.created_at).toLocaleString() }}</span>
                    </template>
                    <template #cell-product="{ item }">{{ item.product?.name || 'N/A' }}</template>
                    <template #cell-type="{ item }">
                        <span class="badge text-capitalize" :class="typeBadges[item.type] || 'badge-secondary'">{{ item.type.replace('_', ' ') }}</span>
                    </template>
                    <template #cell-quantity="{ item }">
                        <span :class="item.quantity >= 0 ? 'text-success' : 'text-danger'" class="font-weight-bold">
                            {{ item.quantity >= 0 ? '+' : '' }}{{ item.quantity }}
                        </span>
                    </template>
                    <template #cell-note="{ item }">
                        <span class="text-xs text-muted">{{ item.note || '—' }}</span>
                    </template>
                </PremiumTable>
            </div>
        </section>

        <!-- Adjust Stock Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title font-weight-bold">Adjust Stock</h5>
                        <button type="button" class="close" @click="showModal = false"><span>&times;</span></button>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="text-xs font-bold text-muted text-uppercase">Quantity Change</label>
                                <input type="number" v-model="form.delta" class="form-control" placeholder="e.g. -5 or 10" :class="{ 'is-invalid': form.errors.delta }">
                                <p class="text-xs text-muted mt-1 mb-0">Positive to add stock, negative to remove it.</p>
                                <span class="text-danger text-sm" v-if="form.errors.delta">{{ form.errors.delta }}</span>
                            </div>
                            <div class="form-group mb-0">
                                <label class="text-xs font-bold text-muted text-uppercase">Reason</label>
                                <textarea v-model="form.note" rows="2" class="form-control" placeholder="e.g. Damaged in warehouse, stock recount" :class="{ 'is-invalid': form.errors.note }"></textarea>
                                <span class="text-danger text-sm" v-if="form.errors.note">{{ form.errors.note }}</span>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" @click="showModal = false">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">Save Adjustment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
