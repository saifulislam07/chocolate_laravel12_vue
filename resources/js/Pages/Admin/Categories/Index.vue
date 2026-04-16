<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    categories: Array
});

const showModal = ref(false);
const isEditing = ref(false);
const imagePreview = ref(null);

const form = useForm({
    id: null,
    name: '',
    description: '',
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

const openEditModal = (category) => {
    isEditing.value = true;
    form.id = category.id;
    form.name = category.name;
    form.description = category.description;
    form.is_active = category.is_active;
    form.image = null;
    form._method = 'put'; // Laravel PUT workaround for files
    imagePreview.value = category.image ? category.image : null;
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
        form.post(route('admin.categories.update', form.id), {
            onSuccess: () => closeModal(),
            forceFormData: true,
        });
    } else {
        form.post(route('admin.categories.store'), {
            onSuccess: () => closeModal(),
            forceFormData: true,
        });
    }
};

function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('admin.categories.destroy', id));
    }
}
</script>

<template>
    <Head title="Manage Categories" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Product Categories</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3 bg-olive">
                            <span class="info-box-icon elevation-1"><i class="fas fa-sitemap"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Categories</span>
                                <span class="info-box-number">{{ categories.length }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-olive card-outline shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title">Category List</h3>
                        <div class="card-tools">
                            <button class="btn btn-primary btn-sm" @click="openCreateModal">
                                <i class="fas fa-plus mr-1"></i> Add Category
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th style="width: 150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(category, index) in categories" :key="category.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>
                                        <div class="text-center" style="width: 50px;">
                                            <img :src="category.image || 'https://via.placeholder.com/50?text=Cat'" class="img-circle" :alt="category.name" style="height: 40px; width: 40px; object-fit: cover;">
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">{{ category.name }}</td>
                                    <td><code>{{ category.slug }}</code></td>
                                    <td>
                                        <span class="badge" :class="category.is_active ? 'badge-success' : 'badge-danger'">
                                            {{ category.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-xs mr-1" @click="openEditModal(category)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-xs" @click="deleteCategory(category.id)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="categories.length === 0">
                                    <td colspan="6" class="text-center p-4 text-muted">No categories found. Click "Add Category" to create one.</td>
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
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title font-weight-bold">{{ isEditing ? 'Edit Category' : 'Add New Category' }}</h5>
                        <button type="button" class="close" @click="closeModal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submitForm">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="cname">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="cname" v-model="form.name" :class="{ 'is-invalid': form.errors.name }" required placeholder="e.g. Chocolates">
                                <span class="error invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</span>
                            </div>
                            
                            <div class="form-group">
                                <label for="cdesc">Description <small class="text-muted">(Optional)</small></label>
                                <textarea class="form-control" id="cdesc" rows="3" v-model="form.description" :class="{ 'is-invalid': form.errors.description }" placeholder="Brief description..."></textarea>
                                <span class="error invalid-feedback" v-if="form.errors.description">{{ form.errors.description }}</span>
                            </div>

                            <div class="form-group">
                                <label>Category Image <small class="text-muted">(Optional)</small></label>
                                <div class="custom-file">
                                    <input type="file" @change="handleFileChange" class="custom-file-input" id="imageFile" accept="image/*">
                                    <label class="custom-file-label" for="imageFile">Choose image...</label>
                                </div>
                                <span class="text-danger text-sm" v-if="form.errors.image">{{ form.errors.image }}</span>
                                <div v-if="imagePreview" class="mt-2 text-center bg-light p-2 border rounded">
                                    <img :src="imagePreview" class="img-fluid rounded" style="max-height: 100px;">
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                    <input type="checkbox" class="custom-control-input" id="isActiveSwitch2" v-model="form.is_active">
                                    <label class="custom-control-label" for="isActiveSwitch2">Make this category active</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <i class="fas fa-save mr-1"></i> {{ isEditing ? 'Update Category' : 'Save Category' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
