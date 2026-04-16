<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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

const deleteExistingImage = (imageId) => {
    if(confirm('Are you sure you want to delete this image?')){
        // In a real app, you'd call an API to delete the image from storage
        // For now, let's keep it simple or implement a delete route
    }
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
                                        <textarea v-model="form.description" class="form-control" rows="5" placeholder="Enter detailed description..."></textarea>
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
                                            <div class="position-relative border bg-white p-1 shadow-sm rounded">
                                                <img :src="img.image_path" class="img-fluid rounded" style="height: 120px; width: 100%; object-fit: contain;">
                                                <span v-if="img.is_primary" class="badge badge-success position-absolute" style="top: 5px; left: 5px;">Main</span>
                                                <!-- Add delete button for images if required -->
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label>Add New Images <small class="text-muted">(Will be appended to current gallery)</small></label>
                                        <div class="custom-file mb-3">
                                            <input type="file" @change="handleFileChange" class="custom-file-input" id="customFile" accept="image/*" multiple>
                                            <label class="custom-file-label" for="customFile">Choose files...</label>
                                        </div>
                                        <div class="invalid-feedback d-block" v-if="form.errors.images">{{ form.errors.images }}</div>
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
                                        <div class="col-6 form-group">
                                            <label>Opening Stock</label>
                                            <input type="number" v-model="form.stock" class="form-control bg-light" readonly>
                                            <small class="text-info"><i class="fas fa-info-circle mr-1"></i> Stock updates via Purchases & Sales</small>
                                        </div>
                                        <div class="col-6 form-group">
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
    </AdminLayout>
</template>
