<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    brands: Array
});

const showModal = ref(false);
const isEditing = ref(false);
const imagePreview = ref(null);

const form = useForm({
    id: null,
    name: '',
    image: null,
    is_active: true,
    _method: 'post', // required for Inertia file uploads on PUT
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
    form._method = 'put'; // tell Laravel it's a PUT
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
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Brands</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-tags"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Brands</span>
                                <span class="info-box-number">{{ brands.length }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Brand List</h3>
                        <div class="card-tools">
                            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                                <i class="fas fa-plus mr-1"></i> Add Brand
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Logo</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th style="width: 150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(brand, index) in brands" :key="brand.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>
                                        <div class="text-center" style="width: 50px;">
                                            <img :src="brand.image || 'https://via.placeholder.com/50'" class="img-thumbnail" :alt="brand.name" style="height: 40px; object-fit: contain;">
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">{{ brand.name }}</td>
                                    <td><code>{{ brand.slug }}</code></td>
                                    <td>
                                        <span class="badge" :class="brand.is_active ? 'badge-success' : 'badge-danger'">
                                            {{ brand.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-xs mr-1" @click="openEditModal(brand)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-xs" @click="deleteBrand(brand.id)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="brands.length === 0">
                                    <td colspan="6" class="text-center p-4 text-muted">No brands found. Click "Add Brand" to create one.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
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
