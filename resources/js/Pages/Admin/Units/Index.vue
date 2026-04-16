<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    units: Array
});

const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: '',
    short_name: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (unit) => {
    isEditing.value = true;
    form.id = unit.id;
    form.name = unit.name;
    form.short_name = unit.short_name;
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.units.update', form.id), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('admin.units.store'), {
            onSuccess: () => closeModal()
        });
    }
};

function deleteUnit(id) {
    if (confirm('Are you sure you want to delete this unit?')) {
        router.delete(route('admin.units.destroy', id));
    }
}
</script>

<template>
    <Head title="Manage Units" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Units</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Unit List</h3>
                        <div class="card-tools">
                            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                                <i class="fas fa-plus mr-1"></i> Add Unit
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Unit Name</th>
                                    <th>Short Name</th>
                                    <th style="width: 150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(unit, index) in units" :key="unit.id">
                                    <td>{{ index + 1 }}</td>
                                    <td class="font-weight-bold">{{ unit.name }}</td>
                                    <td><code>{{ unit.short_name }}</code></td>
                                    <td>
                                        <button class="btn btn-info btn-xs mr-1" @click="openEditModal(unit)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-xs" @click="deleteUnit(unit.id)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="units.length === 0">
                                    <td colspan="4" class="text-center p-4 text-muted">No units found. Click "Add Unit" to create one.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Use native bootstrap modal via Vue conditional rendering -->
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5);" v-if="showModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ isEditing ? 'Edit Unit' : 'Add New Unit' }}</h5>
                        <button type="button" class="close" @click="closeModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submitForm">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="name">Unit Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" v-model="form.name" :class="{ 'is-invalid': form.errors.name }" required placeholder="e.g. Kilogram">
                                <span class="error invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</span>
                            </div>
                            <div class="form-group">
                                <label for="short_name">Short Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="short_name" v-model="form.short_name" :class="{ 'is-invalid': form.errors.short_name }" required placeholder="e.g. kg">
                                <span class="error invalid-feedback" v-if="form.errors.short_name">{{ form.errors.short_name }}</span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <i class="fas fa-save mr-1"></i> {{ isEditing ? 'Update Changes' : 'Save Unit' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
