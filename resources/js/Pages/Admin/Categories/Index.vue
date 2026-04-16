<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '60px' },
    { key: 'image', label: 'Image', sortable: false, width: '80px' },
    { key: 'name', label: 'Category Name', sortable: true },
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
    description: '',
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

const openEditModal = (category) => {
    isEditing.value = true;
    form.id = category.id;
    form.name = category.name;
    form.description = category.description;
    form.is_active = category.is_active;
    form.image = null;
    form._method = 'put';
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Product Categories</h1>
                        <p class="text-muted text-sm mb-0">Organize your products into logical groups</p>
                    </div>
                    <button class="btn btn-primary shadow-sm" @click="openCreateModal">
                        <i class="fas fa-plus mr-2"></i> Add Category
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="premium-stat-card bg-white p-3 rounded-lg shadow-sm border">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary-soft mr-3">
                                    <i class="fas fa-sitemap text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-uppercase font-bold text-muted">Total Categories</div>
                                    <div class="h4 font-bold mb-0">{{ categories.length }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <PremiumTable 
                    :items="categories" 
                    :headers="columns"
                    search-placeholder="Search categories..."
                >
                    <!-- Index Cell -->
                    <template #cell-index="{ index }">
                        <span class="text-muted">{{ index + 1 }}</span>
                    </template>

                    <!-- Image Cell -->
                    <template #cell-image="{ item }">
                        <img :src="item.image || 'https://ui-avatars.com/api/?background=f1f5f9&color=64748b&name=C'" 
                             class="rounded-lg shadow-sm" 
                             style="height: 40px; width: 40px; object-fit: cover;">
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
                            <button @click="deleteCategory(item.id)" class="btn btn-light btn-sm border shadow-none">
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
