<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    product: Object,
    categories: Array,
    brands: Array,
    units: Array,
});

const form = useForm({
    name: props.product.name,
    category_id: props.product.category_id,
    brand_id: props.product.brand_id || '',
    unit_id: props.product.unit_id,
    cost_price: props.product.cost_price,
    price: props.product.price,
    compare_at_price: props.product.compare_at_price || '',
    stock: props.product.stock,
    alert_quantity: props.product.alert_quantity,
    sku: props.product.sku || '',
    description: props.product.description || '',
    images: [],
    is_active: !!props.product.is_active,
    is_featured: !!props.product.is_featured,
    is_new: !!props.product.is_new,
    _method: 'put', // Required for file uploads on update
});

// Laravel keys the `images.*` rule as `images.0`, so look the error up by prefix.
const imageError = computed(() => Object.entries(form.errors).find(([key]) => key.startsWith('images'))?.[1]);

const imagePreviews = ref([]);

const handleFileChange = (e) => {
    const files = Array.from(e.target.files);
    form.images = files;
    
    // Generate previews
    imagePreviews.value = [];
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const imageToDelete = ref(null);
const deletingImage = ref(false);

const confirmDeleteImage = (img) => {
    imageToDelete.value = img;
};

const closeDeleteModal = () => {
    if (!deletingImage.value) imageToDelete.value = null;
};

const deleteExistingImage = () => {
    if (!imageToDelete.value) return;

    deletingImage.value = true;
    router.delete(route('admin.products.images.destroy', [props.product.id, imageToDelete.value.id]), {
        preserveScroll: true,
        onFinish: () => {
            deletingImage.value = false;
            imageToDelete.value = null;
        },
    });
};

const setPrimaryImage = (imageId) => {
    router.patch(route('admin.products.images.primary', [props.product.id, imageId]), {}, {
        preserveScroll: true,
    });
};

const submit = () => {
    form.post(route('admin.products.update', props.product.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit Product" />
    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Edit Product: {{ product.name }}</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <form @submit.prevent="submit">
                    <div class="row">
                        <!-- Left Column: Main Information -->
                        <div class="col-md-8">
                            <div class="card card-primary card-outline shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title">Basic Information</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Product Name <span class="text-danger">*</span></label>
                                        <input type="text" v-model="form.name" class="form-control" :class="{'is-invalid': form.errors.name}" required>
                                        <div class="invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label>Category <span class="text-danger">*</span></label>
                                            <select v-model="form.category_id" class="form-control" :class="{'is-invalid': form.errors.category_id}" required>
                                                <option value="">Select Category</option>
                                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                            </select>
                                            <div class="invalid-feedback" v-if="form.errors.category_id">{{ form.errors.category_id }}</div>
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Brand</label>
                                            <select v-model="form.brand_id" class="form-control" :class="{'is-invalid': form.errors.brand_id}">
                                                <option value="">Select Brand</option>
                                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                                            </select>
                                            <div class="invalid-feedback" v-if="form.errors.brand_id">{{ form.errors.brand_id }}</div>
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Unit <span class="text-danger">*</span></label>
                                            <select v-model="form.unit_id" class="form-control" :class="{'is-invalid': form.errors.unit_id}" required>
                                                <option value="">Select Unit</option>
                                                <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }} ({{ unit.short_name }})</option>
                                            </select>
                                            <div class="invalid-feedback" v-if="form.errors.unit_id">{{ form.errors.unit_id }}</div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <RichTextEditor v-model="form.description" :height="220" :invalid="!!form.errors.description" placeholder="Enter detailed description..." />
                                    </div>
                                </div>
                            </div>

                            <!-- Image Gallery -->
                            <div class="card card-info card-outline shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title">Product Gallery</h3>
                                </div>
                                <div class="card-body">
                                    <!-- Existing Images -->
                                    <div v-if="product.images && product.images.length > 0" class="row mb-4">
                                        <div class="col-12 mb-2"><strong class="text-muted">Currently Active Images:</strong></div>
                                        <div class="col-md-3 col-sm-4 col-6 mb-3" v-for="img in product.images" :key="img.id">
                                            <div class="gallery-tile position-relative border bg-white p-1 shadow-sm rounded">
                                                <img :src="img.image_path" class="img-fluid rounded" style="height: 120px; width: 100%; object-fit: contain;">
                                                <span v-if="img.is_primary" class="badge badge-success position-absolute" style="top: 5px; left: 5px;">Main</span>
                                                <button type="button" class="btn btn-danger tile-delete position-absolute" style="top: 5px; right: 5px;" title="Delete image" @click="confirmDeleteImage(img)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button v-if="!img.is_primary" type="button" class="btn btn-outline-success btn-block tile-primary mt-1" @click="setPrimaryImage(img.id)">
                                                    <i class="fas fa-star mr-1"></i> Set as Main
                                                </button>
                                                <div v-else class="tile-primary-spacer mt-1">Current main image</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label>Add New Images <small class="text-muted">(Will be appended to current gallery)</small></label>
                                        <div class="custom-file mb-3">
                                            <input type="file" @change="handleFileChange" class="custom-file-input" id="customFile" accept="image/*" multiple>
                                            <label class="custom-file-label" for="customFile">Choose files...</label>
                                        </div>
                                        <div class="invalid-feedback d-block" v-if="imageError">{{ imageError }}</div>
                                    </div>
                                    
                                    <div v-if="imagePreviews.length > 0" class="row border rounded p-3 bg-light mt-3 animated fadeIn">
                                        <div class="col-12 mb-2"><strong class="text-success">New Selection Preview:</strong></div>
                                        <div class="col-md-2 col-sm-3 col-4 mb-2 text-center" v-for="(preview, index) in imagePreviews" :key="index">
                                            <div class="position-relative border bg-white p-1 shadow-sm">
                                                <img :src="preview" class="img-fluid" style="height: 80px; object-fit: contain;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Pricing & Options -->
                        <div class="col-md-4">
                            <div class="card card-success card-outline shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title">Pricing & Stock</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Cost Price (৳) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" v-model="form.cost_price" class="form-control" :class="{'is-invalid': form.errors.cost_price}" required>
                                        <div class="invalid-feedback" v-if="form.errors.cost_price">{{ form.errors.cost_price }}</div>
                                    </div>
                                    <div class="form-group">
                                        <label>Selling Price (৳) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" v-model="form.price" class="form-control form-control-lg text-success font-weight-bold" :class="{'is-invalid': form.errors.price}" required>
                                        <div class="invalid-feedback" v-if="form.errors.price">{{ form.errors.price }}</div>
                                    </div>
                                    <div class="form-group">
                                        <label>Compare at Price (৳) <small class="text-muted">(Discounted from)</small></label>
                                        <input type="number" step="0.01" v-model="form.compare_at_price" class="form-control text-muted" style="text-decoration: line-through;">
                                    </div>
                                    <hr>
                                    <div class="form-group">
                                        <label>SKU</label>
                                        <input type="text" v-model="form.sku" class="form-control">
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-6 form-group">
                                            <label>Opening Stock</label>
                                            <input type="number" v-model="form.stock" class="form-control bg-light" readonly>
                                            <small class="text-info"><i class="fas fa-info-circle mr-1"></i> Stock updates via Purchases & Sales</small>
                                        </div>
                                        <div class="col-12 col-md-6 form-group">
                                            <label>Alert Qty</label>
                                            <input type="number" v-model="form.alert_quantity" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-warning card-outline shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title">Status Options</h3>
                                </div>
                                <div class="card-body">
                                    <div class="custom-control custom-switch mb-3">
                                        <input type="checkbox" class="custom-control-input" id="statusSwitch" v-model="form.is_active">
                                        <label class="custom-control-label" for="statusSwitch">Publish (Active)</label>
                                    </div>
                                    <div class="custom-control custom-switch mb-3">
                                        <input type="checkbox" class="custom-control-input" id="featuredSwitch" v-model="form.is_featured">
                                        <label class="custom-control-label" for="featuredSwitch">Featured Product</label>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="newSwitch" v-model="form.is_new">
                                        <label class="custom-control-label" for="newSwitch">Mark as New Arrival</label>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow-none bg-transparent">
                                <Link :href="route('admin.products.index')" class="btn btn-default mb-2">Cancel / Back</Link>
                                <button type="submit" class="btn btn-info btn-lg btn-block shadow" :disabled="form.processing">
                                    <i class="fas fa-save mr-1"></i> Update Product
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- Delete Image Confirmation -->
        <Transition name="confirm-fade">
            <div v-if="imageToDelete" class="modal d-block confirm-backdrop" tabindex="-1" role="dialog" @click.self="closeDeleteModal">
                <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content border-0 shadow-lg confirm-card">
                        <div class="modal-body text-center px-4 pt-4 pb-3">
                            <div class="confirm-icon mb-3">
                                <i class="fas fa-trash-alt"></i>
                            </div>

                            <h5 class="font-weight-bold mb-1">Delete this image?</h5>
                            <p class="text-muted small mb-3">
                                It will be permanently removed from
                                <strong class="text-dark">{{ product.name }}</strong>.
                                This can't be undone.
                            </p>

                            <div class="confirm-thumb mx-auto mb-2">
                                <img :src="imageToDelete.image_path" alt="">
                            </div>
                            <span v-if="imageToDelete.is_primary" class="badge badge-warning">
                                <i class="fas fa-star mr-1"></i> Main image
                            </span>
                            <p v-if="imageToDelete.is_primary" class="text-muted small mt-2 mb-0">
                                The next image in the gallery will become the main one.
                            </p>
                        </div>
                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                            <button type="button" class="btn btn-light border flex-fill" :disabled="deletingImage" @click="closeDeleteModal">
                                Cancel
                            </button>
                            <button type="button" class="btn btn-danger flex-fill shadow-sm" :disabled="deletingImage" @click="deleteExistingImage">
                                <i class="fas mr-1" :class="deletingImage ? 'fa-spinner fa-spin' : 'fa-trash-alt'"></i>
                                {{ deletingImage ? 'Deleting...' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
/* AdminLayout sets a global `.btn { padding: .6rem 1.5rem; border-radius: 10px }` with
   !important, which blows up these tiny tile controls, so they get their own sizing. */
.tile-delete {
    padding: 0.15rem 0.42rem !important;
    font-size: 0.72rem !important;
    line-height: 1.4 !important;
    border-radius: 6px !important;
    z-index: 2;
}

.tile-primary {
    padding: 0.22rem 0.5rem !important;
    font-size: 0.72rem !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
}

/* Keeps the main-image tile the same height as the ones carrying a button. */
.tile-primary-spacer {
    padding: 0.22rem 0.5rem;
    font-size: 0.72rem;
    font-weight: 600;
    line-height: 1.5;
    text-align: center;
    color: #adb5bd;
    border: 1px solid transparent;
}

.confirm-backdrop {
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(2px);
}

.confirm-card {
    border-radius: 12px;
    overflow: hidden;
}

.confirm-icon {
    width: 60px;
    height: 60px;
    line-height: 60px;
    margin: 0 auto;
    border-radius: 50%;
    background: #fdecea;
    color: #dc3545;
    font-size: 24px;
}

.confirm-thumb {
    width: 110px;
    height: 110px;
    padding: 4px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #fff;
}

.confirm-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.confirm-fade-enter-active,
.confirm-fade-leave-active {
    transition: opacity 0.2s ease;
}

.confirm-fade-enter-active .confirm-card,
.confirm-fade-leave-active .confirm-card {
    transition: transform 0.2s ease;
}

.confirm-fade-enter-from,
.confirm-fade-leave-to {
    opacity: 0;
}

.confirm-fade-enter-from .confirm-card,
.confirm-fade-leave-to .confirm-card {
    transform: scale(0.94);
}
</style>
