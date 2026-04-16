<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    units: Array
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '60px' },
    { key: 'name', label: 'Unit Name', sortable: true },
    { key: 'short_name', label: 'Short Name', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' }
];

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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Measurement Units</h1>
                        <p class="text-muted text-sm mb-0">Define units for product weight, volume, or count</p>
                    </div>
                    <button class="btn btn-primary shadow-sm" @click="openCreateModal">
                        <i class="fas fa-plus mr-2"></i> Add Unit
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <PremiumTable 
                    :items="units" 
                    :headers="columns"
                    search-placeholder="Search units..."
                >
                    <!-- Index Cell -->
                    <template #cell-index="{ index }">
                        <span class="text-muted">{{ index + 1 }}</span>
                    </template>

                    <!-- Name Cell -->
                    <template #cell-name="{ item }">
                        <span class="font-weight-bold text-dark">{{ item.name }}</span>
                    </template>

                    <!-- Short Name Cell -->
                    <template #cell-short_name="{ item }">
                        <code class="text-xs px-2 py-1 bg-light rounded text-indigo">{{ item.short_name }}</code>
                    </template>

                    <!-- Actions Cell -->
                    <template #cell-actions="{ item }">
                        <div class="d-flex">
                            <button @click="openEditModal(item)" class="btn btn-light btn-sm mr-2 border shadow-none">
                                <i class="fas fa-edit text-primary text-xs"></i>
                            </button>
                            <button @click="deleteUnit(item.id)" class="btn btn-light btn-sm border shadow-none">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
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
