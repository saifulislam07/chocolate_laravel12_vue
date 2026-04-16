<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sliders: Array
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '60px' },
    { key: 'image', label: 'Image', sortable: false, width: '120px' },
    { key: 'title', label: 'Title', sortable: true },
    { key: 'sort_order', label: 'Order', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false, width: '150px' }
];

const showModal = ref(false);
const editMode = ref(false);
const currentId = ref(null);
const imagePreview = ref(null);

const form = useForm({
    title: '',
    subtitle: '',
    description: '',
    image: null,
    bg_color: '#FBE0E3',
    text_color: '#1C1C1C',
    button_text: 'Shop Now',
    button_link: '#',
    sort_order: 0,
    is_active: true,
});

function openCreateModal() {
    editMode.value = false;
    currentId.value = null;
    imagePreview.ref = null;
    form.clearErrors();
    form.reset();
    showModal.value = true;
}

function openEditModal(slider) {
    editMode.value = true;
    currentId.value = slider.id;
    imagePreview.value = slider.image;
    form.clearErrors();
    form.title = slider.title;
    form.subtitle = slider.subtitle;
    form.description = slider.description;
    form.bg_color = slider.bg_color || '#FBE0E3';
    form.text_color = slider.text_color || '#1C1C1C';
    form.button_text = slider.button_text || 'Shop Now';
    form.button_link = slider.button_link || '#';
    form.sort_order = slider.sort_order;
    form.is_active = !!slider.is_active;
    form.image = null; // Don't reset image in edit mode unless uploading new
    showModal.value = true;
}

function submit() {
    if (editMode.value) {
        // Use POST with _method=PUT for file uploads in updates (PHP/Laravel requirement for multipart)
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route('admin.sliders.update', currentId.value), {
            forceFormData: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.sliders.store'), {
            onSuccess: () => closeModal(),
        });
    }
}

function closeModal() {
    showModal.value = false;
    form.reset();
    imagePreview.value = null;
}

function deleteSlider(id) {
    if (confirm('Are you sure you want to delete this slider?')) {
        router.delete(route('admin.sliders.destroy', id));
    }
}

function handleImageChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}
</script>

<template>
    <Head title="Manage Sliders" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3 text-slate-800">Home Sliders</h1>
                        <p class="text-muted text-sm mb-0">Manage promotional banners on the homepage</p>
                    </div>
                    <button @click="openCreateModal" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus mr-2"></i> Add New Slider
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <PremiumTable 
                    :items="sliders" 
                    :headers="columns"
                    search-placeholder="Search sliders..."
                >
                    <!-- Index Cell -->
                    <template #cell-index="{ index }">
                        <span class="text-muted">{{ index + 1 }}</span>
                    </template>

                    <!-- Image Cell -->
                    <template #cell-image="{ item }">
                        <div class="p-1 rounded bg-light border d-inline-block shadow-xs">
                             <img :src="item.image" :alt="item.title" class="rounded" style="height: 35px; width: 70px; object-fit: cover;" v-if="item.image">
                             <div v-else class="bg-gray-200 rounded text-[10px] text-center" style="width: 70px; height: 35px; line-height: 35px;">No Image</div>
                        </div>
                    </template>

                    <!-- Title Cell -->
                    <template #cell-title="{ item }">
                        <div>
                            <div class="font-weight-bold text-dark truncate" style="max-width: 250px;" v-html="item.title"></div>
                            <div class="text-[10px] text-muted tracking-tight mt-1">{{ item.subtitle }}</div>
                        </div>
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
                            <button @click="openEditModal(item)" class="btn btn-light btn-sm mr-2 border shadow-none" title="Edit">
                                <i class="fas fa-edit text-primary text-xs"></i>
                            </button>
                            <button @click="deleteSlider(item.id)" class="btn btn-light btn-sm border shadow-none" title="Delete">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>

        <!-- Slider Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom py-3 px-4 bg-light">
                        <h5 class="modal-title font-bold text-dark h6">
                            <i class="fas mr-2" :class="editMode ? 'fa-edit text-primary' : 'fa-plus text-success'"></i> 
                            {{ editMode ? 'Modify Slider Content' : 'Add New Promotional Slider' }}
                        </h5>
                        <button type="button" class="close" @click="closeModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="modal-body p-4">
                            <div class="row">
                                <!-- Content Section -->
                                <div class="col-lg-7">
                                    <div class="card bg-light border-0 mb-4 rounded-lg">
                                        <div class="card-body p-4">
                                            <div class="form-group mb-3">
                                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider">Slider Title (HTML support)</label>
                                                <input type="text" v-model="form.title" class="form-control border-2" placeholder="e.g. Legacy made in <br/> chocolate" required>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 form-group mb-3">
                                                    <label class="text-xs font-bold text-muted text-uppercase tracking-wider">Subtitle / Tagline</label>
                                                    <input type="text" v-model="form.subtitle" class="form-control" placeholder="e.g. Exquisite Gifting">
                                                </div>
                                                <div class="col-md-6 form-group mb-3">
                                                    <label class="text-xs font-bold text-muted text-uppercase tracking-wider">Button Text</label>
                                                    <input type="text" v-model="form.button_text" class="form-control" placeholder="Shop Now">
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider">Description</label>
                                                <textarea v-model="form.description" class="form-control" rows="3" placeholder="Short marketing text..."></textarea>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider">Button Link (URL)</label>
                                                <input type="text" v-model="form.button_link" class="form-control" placeholder="https://...">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label class="text-xs font-bold text-muted text-uppercase tracking-wider">Sort Order</label>
                                            <input type="number" v-model="form.sort_order" class="form-control">
                                        </div>
                                        <div class="col-md-4 form-group pt-4">
                                            <div class="custom-control custom-switch mt-2">
                                                <input type="checkbox" v-model="form.is_active" class="custom-control-input" id="sliderStatus">
                                                <label class="custom-control-label font-bold text-sm" for="sliderStatus">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Visuals Section -->
                                <div class="col-lg-5">
                                    <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-2 d-block">Slider Appearance</label>
                                    <div class="p-3 border rounded-lg mb-4" :style="{ backgroundColor: form.bg_color || '#eee', color: form.text_color || '#000' }">
                                        <div class="text-[10px] uppercase font-bold tracking-tighter mb-2">Live Preview Area</div>
                                        <div class="h-40 flex items-center justify-center overflow-hidden rounded bg-white/20 border border-white/30 relative">
                                            <img v-if="imagePreview" :src="imagePreview" class="max-h-full object-contain z-10">
                                            <i v-else class="fas fa-image fa-3x opacity-20"></i>
                                            <span class="absolute bottom-2 right-2 text-[10px] font-bold opacity-50">{{ form.button_text }}</span>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-6">
                                                <label class="text-[10px] font-bold uppercase opacity-70">Background</label>
                                                <input type="color" v-model="form.bg_color" class="form-control form-control-sm h-8 cursor-pointer">
                                            </div>
                                            <div class="col-6">
                                                <label class="text-[10px] font-bold uppercase opacity-70">Text Color</label>
                                                <input type="color" v-model="form.text_color" class="form-control form-control-sm h-8 cursor-pointer">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="text-xs font-bold text-muted text-uppercase tracking-wider">Upload Main Image</label>
                                        <div class="custom-file">
                                            <input type="file" @change="handleImageChange" class="custom-file-input" id="sliderImage" accept="image/*">
                                            <label class="custom-file-label text-truncate text-xs pt-2" for="sliderImage">
                                                {{ form.image ? form.image.name : 'Choose elegant image...' }}
                                            </label>
                                        </div>
                                        <small class="text-muted text-[10px] mt-1 d-block italic">Recommended size: 800x800px or larger with clean background.</small>
                                        <div class="text-danger text-xs mt-1" v-if="form.errors.image">{{ form.errors.image }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top p-4 bg-light">
                            <button type="button" class="btn btn-light px-4 border" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm" :disabled="form.processing">
                                <i class="fas fa-save mr-2"></i> {{ editMode ? 'Update Slider' : 'Publish Slider' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.modal.show {
    display: block;
}

.shadow-xs { box-shadow: 0 1px 3px rgba(0,0,0,0.05); }

.bg-light-gray { background-color: #f8fafc; }

.hover-bg-gray:hover { background-color: #f1f5f9; }

.cursor-pointer { cursor: pointer; }

/* Custom Switch Styling */
.custom-switch .custom-control-label::before {
    height: 1.5rem;
    width: 2.75rem;
    border-radius: 2rem;
}

.custom-switch .custom-control-label::after {
    width: calc(1.5rem - 4px);
    height: calc(1.5rem - 4px);
    background-color: #adb5bd;
    border-radius: 2rem;
    top: calc(0.25rem + 2px);
    left: calc(-2.25rem + 2px);
}

.custom-switch .custom-control-input:checked ~ .custom-control-label::after {
    background-color: #fff;
    transform: translateX(1.15rem);
}

.custom-switch .custom-control-input:checked ~ .custom-control-label::before {
    border-color: #28a745;
    background-color: #28a745;
}
</style>
