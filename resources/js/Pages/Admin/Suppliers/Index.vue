<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    suppliers: Array
});

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
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Suppliers</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3 bg-indigo shadow-sm">
                            <span class="info-box-icon elevation-1"><i class="fas fa-truck"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Suppliers</span>
                                <span class="info-box-number">{{ suppliers.length }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-indigo card-outline shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">Supplier List</h3>
                        <div class="card-tools">
                            <button class="btn btn-primary btn-sm rounded-pill px-3" @click="openCreateModal">
                                <i class="fas fa-plus mr-1"></i> Add Supplier
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped table-valign-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Supplier Name</th>
                                    <th>Company</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th style="width: 150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(supplier, index) in suppliers" :key="supplier.id">
                                    <td>{{ index + 1 }}</td>
                                    <td class="font-weight-bold text-indigo">{{ supplier.name }}</td>
                                    <td>{{ supplier.company_name || 'N/A' }}</td>
                                    <td><i class="fas fa-phone-alt text-muted mr-1"></i> {{ supplier.phone }}</td>
                                    <td><i class="fas fa-envelope text-muted mr-1"></i> {{ supplier.email || 'N/A' }}</td>
                                    <td><small>{{ supplier.address || 'N/A' }}</small></td>
                                    <td>
                                        <button class="btn btn-outline-info btn-xs mr-1 p-1 px-2" @click="openEditModal(supplier)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-xs p-1 px-2" @click="deleteSupplier(supplier.id)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="suppliers.length === 0">
                                    <td colspan="7" class="text-center p-5 text-muted">
                                        <i class="fas fa-users-slash fa-2x mb-2 d-block opacity-50"></i>
                                        No suppliers found. Click "Add Supplier" to get started.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
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
