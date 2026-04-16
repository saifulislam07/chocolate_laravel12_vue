<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    suppliers: Array
});

const columns = [
    { key: 'name', label: 'Supplier Name', sortable: true },
    { key: 'company_name', label: 'Company', sortable: true },
    { key: 'phone', label: 'Phone', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'address', label: 'Address', sortable: false },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' }
];

const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: '',
    company_name: '',
    phone: '',
    email: '',
    address: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (supplier) => {
    isEditing.value = true;
    form.id = supplier.id;
    form.name = supplier.name;
    form.company_name = supplier.company_name;
    form.phone = supplier.phone;
    form.email = supplier.email;
    form.address = supplier.address;
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.suppliers.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.suppliers.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

function deleteSupplier(id) {
    if (confirm('Are you sure you want to delete this supplier?')) {
        router.delete(route('admin.suppliers.destroy', id), {
            preserveScroll: true
        });
    }
}
</script>

<template>
    <Head title="Manage Suppliers" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Suppliers</h1>
                        <p class="text-muted text-sm mb-0">Manage your business partners and item providers</p>
                    </div>
                    <button class="btn btn-primary shadow-sm" @click="openCreateModal">
                        <i class="fas fa-plus mr-2"></i> Add Supplier
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="premium-stat-card bg-white p-3 rounded-lg shadow-sm border border-indigo-soft">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-indigo-soft mr-3">
                                    <i class="fas fa-truck text-indigo"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-uppercase font-bold text-muted">Total Suppliers</div>
                                    <div class="h4 font-bold mb-0 text-indigo">{{ suppliers.length }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <PremiumTable 
                    :items="suppliers" 
                    :headers="columns"
                    search-placeholder="Search suppliers by name or company..."
                >
                    <!-- Supplier Name Cell -->
                    <template #cell-name="{ item }">
                        <div class="font-weight-bold text-dark">{{ item.name }}</div>
                        <div class="text-xs text-muted" v-if="item.company_name">{{ item.company_name }}</div>
                    </template>

                    <!-- Company Cell -->
                    <template #cell-company_name="{ item }">
                        <span class="text-muted text-sm">{{ item.company_name || 'Individual' }}</span>
                    </template>

                    <!-- Phone Cell -->
                    <template #cell-phone="{ item }">
                        <div class="d-flex align-items-center text-sm">
                            <i class="fas fa-phone-alt mr-2 text-muted text-xs"></i>
                            {{ item.phone }}
                        </div>
                    </template>

                    <!-- Email Cell -->
                    <template #cell-email="{ item }">
                        <div class="d-flex align-items-center text-sm" v-if="item.email">
                            <i class="fas fa-envelope mr-2 text-muted text-xs"></i>
                            {{ item.email }}
                        </div>
                        <span v-else class="text-muted text-xs italic">No email</span>
                    </template>

                    <!-- Address Cell -->
                    <template #cell-address="{ item }">
                        <div class="text-xs text-muted text-truncate" style="max-width: 200px;" :title="item.address">
                            {{ item.address || 'N/A' }}
                        </div>
                    </template>

                    <!-- Actions Cell -->
                    <template #cell-actions="{ item }">
                        <div class="d-flex">
                            <button @click="openEditModal(item)" class="btn btn-light btn-sm mr-2 border shadow-none" title="Edit">
                                <i class="fas fa-edit text-primary text-xs"></i>
                            </button>
                            <button @click="deleteSupplier(item.id)" class="btn btn-light btn-sm border shadow-none" title="Delete">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>

        <!-- Vue Modal for Create/Edit -->
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5);" v-if="showModal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas mr-2" :class="isEditing ? 'fa-user-edit' : 'fa-user-plus'"></i>
                            {{ isEditing ? 'Edit Supplier' : 'Add New Supplier' }}
                        </h5>
                        <button type="button" class="close text-white" @click="closeModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submitForm">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Supplier Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" v-model="form.name" :class="{ 'is-invalid': form.errors.name }" required placeholder="e.g. John Doe">
                                        <span class="error invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Company Name</label>
                                        <input type="text" class="form-control" v-model="form.company_name" :class="{ 'is-invalid': form.errors.company_name }" placeholder="e.g. Acme Corp">
                                        <span class="error invalid-feedback" v-if="form.errors.company_name">{{ form.errors.company_name }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                                            <input type="text" class="form-control" v-model="form.phone" :class="{ 'is-invalid': form.errors.phone }" required placeholder="017xxxxxxxx">
                                        </div>
                                        <span class="text-danger text-sm" v-if="form.errors.phone">{{ form.errors.phone }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email Address</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                                            <input type="email" class="form-control" v-model="form.email" :class="{ 'is-invalid': form.errors.email }" placeholder="supplier@example.com">
                                        </div>
                                        <span class="text-danger text-sm" v-if="form.errors.email">{{ form.errors.email }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label>Full Address</label>
                                <textarea class="form-control" v-model="form.address" rows="3" placeholder="Enter complete address details..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary px-4" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm" :disabled="form.processing">
                                <i class="fas fa-save mr-1"></i> {{ isEditing ? 'Update Supplier' : 'Save Supplier' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
