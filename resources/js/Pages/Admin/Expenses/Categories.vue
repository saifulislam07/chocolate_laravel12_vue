<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    categories: Array
});

const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (category) => {
    isEditing.value = true;
    form.id = category.id;
    form.name = category.name;
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.expense-categories.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.expense-categories.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCategory = (id) => {
    if (confirm('Are you sure? This will delete the category.')) {
        form.delete(route('admin.expense-categories.destroy', id));
    }
};
</script>

<template>
    <Head title="Expense Categories" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-tags mr-2 text-danger"></i>Expense Categories</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-danger card-outline shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">Category List</h3>
                        <div class="card-tools">
                            <button class="btn btn-danger btn-sm rounded-pill px-3" @click="openCreateModal">
                                <i class="fas fa-plus mr-1"></i> New Category
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped table-valign-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Category Name</th>
                                    <th class="text-center">Total Expenses</th>
                                    <th style="width: 150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(category, index) in categories" :key="category.id">
                                    <td>{{ index + 1 }}</td>
                                    <td class="font-weight-bold text-dark">{{ category.name }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-info shadow-sm">{{ category.expenses_count }} Records</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-outline-primary btn-xs mr-1 p-1 px-2" @click="openEditModal(category)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-xs p-1 px-2" @click="deleteCategory(category.id)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="categories.length === 0">
                                    <td colspan="4" class="text-center p-5 text-muted">No categories found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Category Modal -->
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5);" v-if="showModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas mr-2" :class="isEditing ? 'fa-edit' : 'fa-plus'"></i>
                            {{ isEditing ? 'Edit Category' : 'Add New Category' }}
                        </h5>
                        <button type="button" class="close text-white" @click="closeModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submitForm">
                        <div class="modal-body bg-light">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" v-model="form.name" :class="{ 'is-invalid': form.errors.name }" required placeholder="e.g. Utility Bills">
                                <span class="error invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</span>
                            </div>
                        </div>
                        <div class="modal-footer bg-white border-0" style="border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-secondary px-4 shadow-sm" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-danger px-4 shadow-sm" :disabled="form.processing">
                                <i class="fas fa-save mr-1"></i> {{ isEditing ? 'Update Category' : 'Save Category' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
