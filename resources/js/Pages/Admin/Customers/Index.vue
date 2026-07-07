<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    customers: { type: Array, default: () => [] },
    divisions: { type: Array, default: () => [] },
});

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'phone', label: 'Phone', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'location', label: 'Location', sortable: false },
    { key: 'balance', label: 'Wallet Balance', sortable: true, cellClass: 'text-right' },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' },
];

const showModal = ref(false);
const isEditing = ref(false);
const currentId = ref(null);

const form = useForm({
    name: '',
    phone: '',
    email: '',
    address: '',
    division_id: '',
    district_id: '',
});

const districtOptions = computed(() => {
    const division = props.divisions.find((d) => String(d.id) === String(form.division_id));
    return division?.districts || [];
});

function onDivisionChange() {
    form.district_id = '';
}

function openCreateModal() {
    isEditing.value = false;
    currentId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEditModal(customer) {
    isEditing.value = true;
    currentId.value = customer.id;
    form.name = customer.name;
    form.phone = customer.phone;
    form.email = customer.email;
    form.address = customer.address;
    form.division_id = customer.division_id || '';
    form.district_id = customer.district_id || '';
    form.clearErrors();
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
}

function submit() {
    if (isEditing.value) {
        form.put(route('admin.customers.update', currentId.value), { onSuccess: () => closeModal() });
    } else {
        form.post(route('admin.customers.store'), { onSuccess: () => closeModal() });
    }
}

function deleteCustomer(id) {
    if (confirm('Are you sure you want to delete this customer?')) {
        router.delete(route('admin.customers.destroy', id));
    }
}

function formatMoney(value) {
    return '৳' + Number(value || 0).toFixed(2);
}
</script>

<template>
    <Head title="Customers" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Customers</h1>
                        <p class="text-muted text-sm mb-0">Customer directory and wallet balances</p>
                    </div>
                    <button @click="openCreateModal" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus mr-2"></i> Add Customer
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <PremiumTable :items="customers" :headers="columns" search-placeholder="Search customers...">
                    <template #cell-name="{ item }">
                        <span class="font-weight-bold text-dark">{{ item.name }}</span>
                    </template>
                    <template #cell-location="{ item }">
                        <span class="text-xs text-muted">{{ item.district?.name ? item.district.name + ', ' + (item.division?.name || '') : '—' }}</span>
                    </template>
                    <template #cell-balance="{ item }">
                        <span class="badge" :class="Number(item.balance) > 0 ? 'badge-success' : 'badge-secondary'">
                            {{ formatMoney(item.balance) }}
                        </span>
                    </template>
                    <template #cell-actions="{ item }">
                        <div class="d-flex">
                            <button @click="openEditModal(item)" class="btn btn-light btn-sm mr-2 border shadow-none">
                                <i class="fas fa-edit text-primary text-xs"></i>
                            </button>
                            <button @click="deleteCustomer(item.id)" class="btn btn-light btn-sm border shadow-none">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>

        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title font-weight-bold">{{ isEditing ? 'Edit Customer' : 'Add New Customer' }}</h5>
                        <button type="button" class="close" @click="closeModal"><span>&times;</span></button>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="text-xs font-bold text-muted text-uppercase">Name</label>
                                <input type="text" v-model="form.name" class="form-control" :class="{ 'is-invalid': form.errors.name }">
                                <span class="text-danger text-sm" v-if="form.errors.name">{{ form.errors.name }}</span>
                            </div>
                            <div class="form-group">
                                <label class="text-xs font-bold text-muted text-uppercase">Phone</label>
                                <input type="text" v-model="form.phone" class="form-control" :class="{ 'is-invalid': form.errors.phone }">
                                <span class="text-danger text-sm" v-if="form.errors.phone">{{ form.errors.phone }}</span>
                            </div>
                            <div class="form-group">
                                <label class="text-xs font-bold text-muted text-uppercase">Email (optional)</label>
                                <input type="email" v-model="form.email" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="text-xs font-bold text-muted text-uppercase">Address (optional)</label>
                                <textarea v-model="form.address" rows="2" class="form-control"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-6 form-group">
                                    <label class="text-xs font-bold text-muted text-uppercase">Division</label>
                                    <select v-model="form.division_id" @change="onDivisionChange" class="form-control">
                                        <option value="">Select Division</option>
                                        <option v-for="division in divisions" :key="division.id" :value="division.id">{{ division.name }}</option>
                                    </select>
                                </div>
                                <div class="col-6 form-group mb-0">
                                    <label class="text-xs font-bold text-muted text-uppercase">District</label>
                                    <select v-model="form.district_id" :disabled="!form.division_id" class="form-control">
                                        <option value="">Select District</option>
                                        <option v-for="district in districtOptions" :key="district.id" :value="district.id">{{ district.name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">{{ isEditing ? 'Update' : 'Save' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
