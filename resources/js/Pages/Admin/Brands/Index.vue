<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    brands: Array
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '60px' },
    { key: 'image', label: 'Logo', sortable: false, width: '80px' },
    { key: 'name', label: 'Brand Name', sortable: true },
    { key: 'slug', label: 'Slug', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' }
];

const showModal = ref(false);
const isEditing = ref(false);
const imagePreview = ref(null);

const form = useForm({
    id: null,
    name: '',
    image: null,
    is_active: true,
    _method: 'post',
});

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if(file){
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => imagePreview.value = e.target.result;
        reader.readAsDataURL(file);
    }
};

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.image = null;
    form._method = 'post';
    imagePreview.value = null;
    const fileInput = document.getElementById('imageFile');
    if (fileInput) fileInput.value = '';
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (brand) => {
    isEditing.value = true;
    form.id = brand.id;
    form.name = brand.name;
    form.is_active = brand.is_active;
    form.image = null;
    form._method = 'put';
    imagePreview.value = brand.image ? brand.image : null;
    const fileInput = document.getElementById('imageFile');
    if (fileInput) fileInput.value = '';
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.post(route('admin.brands.update', form.id), {
            onSuccess: () => closeModal(),
            forceFormData: true,
        });
    } else {
        form.post(route('admin.brands.store'), {
            onSuccess: () => closeModal(),
            forceFormData: true,
        });
    }
};

function deleteBrand(id) {
    if (confirm('Are you sure you want to delete this brand?')) {
        router.delete(route('admin.brands.destroy', id));
    }
}
</script>

<template>
    <Head title="Manage Brands" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Product Brands</h1>
                        <p class="text-muted text-sm mb-0">Manage manufacturer and brand information</p>
                    </div>
                    <button class="btn btn-primary shadow-sm" @click="openCreateModal">
                        <i class="fas fa-plus mr-2"></i> Add Brand
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="premium-stat-card bg-white p-3 rounded-lg shadow-sm border border-warning-soft">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-warning-soft mr-3">
                                    <i class="fas fa-tags text-warning"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-uppercase font-bold text-muted">Total Brands</div>
                                    <div class="h4 font-bold mb-0 text-warning">{{ brands.length }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <PremiumTable 
                    :items="brands" 
                    :headers="columns"
                    search-placeholder="Search brands..."
                >
                    <!-- Index Cell -->
                    <template #cell-index="{ index }">
                        <span class="text-muted">{{ index + 1 }}</span>
                    </template>

                    <!-- Logo Cell -->
                    <template #cell-image="{ item }">
                        <div class="bg-light rounded p-1 d-inline-block border">
                            <img :src="item.image || 'https://ui-avatars.com/api/?background=f1f5f9&color=64748b&name=B'" 
                                 class="rounded" 
                                 style="height: 32px; width: 60px; object-fit: contain;">
                        </div>
                    </template>

                    <!-- Name Cell -->
                    <template #cell-name="{ item }">
                        <span class="font-weight-bold text-dark">{{ item.name }}</span>
                    </template>

                    <!-- Slug Cell -->
                    <template #cell-slug="{ item }">
                        <code class="text-xs px-2 py-1 bg-light rounded text-indigo">{{ item.slug }}</code>
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
                            <button @click="openEditModal(item)" class="btn btn-light btn-sm mr-2 border shadow-none">
                                <i class="fas fa-edit text-primary text-xs"></i>
                            </button>
                            <button @click="deleteBrand(item.id)" class="btn btn-light btn-sm border shadow-none">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>

        <!-- Vue Modal for Create/Edit -->
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5);" v-if="showModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ isEditing ? 'Edit Brand' : 'Add New Brand' }}</h5>
                        <button type="button" class="close" @click="closeModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submitForm">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="bname">Brand Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="bname" v-model="form.name" :class="{ 'is-invalid': form.errors.name }" required placeholder="e.g. Samsung">
                                <span class="error invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</span>
                            </div>
                            
                            <div class="form-group">
                                <label>Brand Logo <small class="text-muted">(Optional)</small></label>
                                <div class="custom-file">
                                    <input type="file" @change="handleFileChange" class="custom-file-input" id="imageFile" accept="image/*">
                                    <label class="custom-file-label" for="imageFile">Choose file...</label>
                                </div>
                                <span class="text-danger text-sm" v-if="form.errors.image">{{ form.errors.image }}</span>
                                <div v-if="imagePreview" class="mt-2">
                                    <img :src="imagePreview" class="img-thumbnail" style="max-height: 80px;">
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="isActiveSwitch" v-model="form.is_active">
                                    <label class="custom-control-label" for="isActiveSwitch">Active Status</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <i class="fas fa-save mr-1"></i> {{ isEditing ? 'Update Brand' : 'Save Brand' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
