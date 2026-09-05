<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    categories: Array,
    brands: Array,
    units: Array,
});

const form = useForm({
    name: '',
    category_id: '',
    brand_id: '',
    unit_id: '',
    cost_price: '',
    price: '',
    compare_at_price: '',
    stock: '',
    alert_quantity: '10',
    sku: '',
    description: '',
    highlights: [''],
    images: [],
    is_active: true,
    is_featured: false,
    is_new: true,
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

const addHighlight = () => {
    if (form.highlights.length < 6) {
        form.highlights.push('');
    }
};

// One row always stays on screen, so the last highlight cannot be removed.
const removeHighlight = (index) => {
    if (form.highlights.length < 2) {
        return;
    }

    form.highlights.splice(index, 1);
};

const submit = () => {
    form.post(route('admin.products.store'));
};
</script>

<template>
    <Head title="Create Product" />
    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Add New Product</h1>
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

                                    <div class="form-group">
                                        <label class="mb-1">Product Highlights <small class="text-muted">(Small bullet points under the buy buttons)</small></label>
                                        <p class="text-muted small mb-2">Up to 6 short phrases. Leave them empty to fall back to the default: Belgian Heritage &amp; Premium Ingredients.</p>

                                        <div v-for="(highlight, index) in form.highlights" :key="index" class="d-flex align-items-start mb-2">
                                            <span class="highlight-index text-muted mr-2">{{ index + 1 }}.</span>
                                            <div class="flex-grow-1">
                                                <input
                                                    type="text"
                                                    v-model="form.highlights[index]"
                                                    class="form-control"
                                                    :class="{ 'is-invalid': form.errors[`highlights.${index}`] }"
                                                    maxlength="60"
                                                    placeholder="e.g. Belgian Heritage"
                                                >
                                                <div class="invalid-feedback" v-if="form.errors[`highlights.${index}`]">{{ form.errors[`highlights.${index}`] }}</div>
                                            </div>
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary highlight-remove ml-2"
                                                :class="{ invisible: form.highlights.length < 2 }"
                                                :disabled="form.highlights.length < 2"
                                                :title="form.highlights.length < 2 ? 'At least one highlight row is kept' : 'Remove this highlight'"
                                                @click="removeHighlight(index)"
                                            >
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>

                                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="addHighlight" :disabled="form.highlights.length >= 6">
                                            <i class="fas fa-plus mr-1"></i> Add Highlight
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-info card-outline shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title">Multiple Image Gallery</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-0">
                                        <label>Upload Images <small class="text-muted">(You can select multiple images)</small></label>
                                        <div class="custom-file mb-3">
                                            <input type="file" @change="handleFileChange" class="custom-file-input" id="customFile" accept="image/*" multiple>
                                            <label class="custom-file-label" for="customFile">Choose files...</label>
                                        </div>
                                        <div class="invalid-feedback d-block" v-if="imageError">{{ imageError }}</div>
                                    </div>
                                    
                                    <div v-if="imagePreviews.length > 0" class="row border rounded p-3 bg-light mt-3">
                                        <div class="col-12 mb-2"><strong class="text-muted">Image Previews:</strong></div>
                                        <div class="col-md-2 col-sm-3 col-4 mb-2 text-center" v-for="(preview, index) in imagePreviews" :key="index">
                                            <div class="position-relative border bg-white p-1 shadow-sm">
                                                <img :src="preview" class="img-fluid" style="height: 100px; object-fit: contain;">
                                                <span v-if="index === 0" class="badge badge-success position-absolute" style="top: -5px; left: -5px;">Primary</span>
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
                                        <label>Compare at Price (৳) <small class="text-muted">(Strike-through)</small></label>
                                        <input type="number" step="0.01" v-model="form.compare_at_price" class="form-control text-muted" style="text-decoration: line-through;">
                                    </div>
                                    <hr>
                                    <div class="form-group">
                                        <label>SKU (Stock Keeping Unit)</label>
                                        <input type="text" v-model="form.sku" class="form-control" placeholder="Optional">
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-6 form-group">
                                            <label>Opening Stock <span class="text-danger">*</span></label>
                                            <input type="number" v-model="form.stock" class="form-control" :class="{'is-invalid': form.errors.stock}" required>
                                            <div class="invalid-feedback" v-if="form.errors.stock">{{ form.errors.stock }}</div>
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
                                <Link :href="route('admin.products.index')" class="btn btn-default mb-2">Cancel</Link>
                                <button type="submit" class="btn btn-primary btn-lg btn-block shadow" :disabled="form.processing">
                                    <i class="fas fa-save mr-1"></i> Save Product
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.highlight-index {
    width: 1.25rem;
    line-height: calc(2.25rem + 2px);
    text-align: right;
}

/* AdminLayout forces a global `.btn { padding: .6rem 1.5rem; border-radius: 10px }`
   with !important, which makes this icon button taller than the input beside it. */
.highlight-remove {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: calc(2.25rem + 2px);
    height: calc(2.25rem + 2px);
    padding: 0 !important;
    border-radius: 0.25rem !important;
}

.highlight-remove:hover:not(:disabled) {
    color: #dc3545;
    border-color: #dc3545;
    background-color: transparent;
}
</style>
