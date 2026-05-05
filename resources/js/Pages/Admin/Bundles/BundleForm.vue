<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    title: String,
    bundle: { type: Object, default: null },
    products: { type: Array, default: () => [] },
    submitRoute: String,
    method: { type: String, default: 'post' },
});

const existingItems = props.bundle?.bundle_items?.map((item) => ({
    product_id: item.id,
    quantity: item.pivot?.quantity || item.quantity || 1,
})) || [{ product_id: '', quantity: 1 }];

const form = useForm({
    name: props.bundle?.name || '',
    sku: props.bundle?.sku || '',
    description: props.bundle?.description || '',
    bundle_note: props.bundle?.bundle_note || '',
    discount_type: props.bundle?.bundle_discount_type || 'fixed',
    discount_value: props.bundle?.bundle_discount_value || 0,
    stock: props.bundle?.stock ?? 0,
    alert_quantity: props.bundle?.alert_quantity ?? 10,
    items: existingItems,
    images: [],
    is_active: props.bundle ? !!props.bundle.is_active : true,
    is_featured: props.bundle ? !!props.bundle.is_featured : false,
    is_new: props.bundle ? !!props.bundle.is_new : true,
    ...(props.method === 'put' ? { _method: 'put' } : {}),
});

const imagePreviews = ref([]);

const selectedProducts = computed(() => new Set(form.items.map((item) => Number(item.product_id)).filter(Boolean)));
const productById = computed(() => new Map(props.products.map((product) => [Number(product.id), product])));

const subtotal = computed(() => form.items.reduce((total, item) => {
    const product = productById.value.get(Number(item.product_id));
    return total + (Number(product?.price || 0) * Number(item.quantity || 0));
}, 0));

const discountAmount = computed(() => {
    const value = Number(form.discount_value || 0);
    return form.discount_type === 'percent' ? subtotal.value * Math.min(value, 100) / 100 : value;
});

const finalPrice = computed(() => Math.max(subtotal.value - discountAmount.value, 0));

function formatMoney(value) {
    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        minimumFractionDigits: 0,
    }).format(Number(value || 0));
}

function addItem() {
    form.items.push({ product_id: '', quantity: 1 });
}

function removeItem(index) {
    if (form.items.length === 1) return;
    form.items.splice(index, 1);
}

function isProductDisabled(productId, currentIndex) {
    return form.items.some((item, index) => index !== currentIndex && Number(item.product_id) === Number(productId));
}

function handleFileChange(event) {
    const files = Array.from(event.target.files);
    form.images = files;
    imagePreviews.value = [];

    files.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => imagePreviews.value.push(e.target.result);
        reader.readAsDataURL(file);
    });
}

function submit() {
    form.post(props.submitRoute, { forceFormData: true });
}
</script>

<template>
    <Head :title="title" />
    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">{{ title }}</h1>
                        <p class="text-muted text-sm mb-0">Select products, add a note, and apply an optional bundle discount.</p>
                    </div>
                    <Link :href="route('admin.bundles.index')" class="btn btn-default">Back</Link>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Bundle Details</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Bundle Name <span class="text-danger">*</span></label>
                                        <input v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }" required>
                                        <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>SKU</label>
                                            <input v-model="form.sku" type="text" class="form-control" :class="{ 'is-invalid': form.errors.sku }">
                                            <div v-if="form.errors.sku" class="invalid-feedback">{{ form.errors.sku }}</div>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Stock <span class="text-danger">*</span></label>
                                            <input v-model="form.stock" type="number" min="0" class="form-control" :class="{ 'is-invalid': form.errors.stock }" required>
                                            <div v-if="form.errors.stock" class="invalid-feedback">{{ form.errors.stock }}</div>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Alert Qty</label>
                                            <input v-model="form.alert_quantity" type="number" min="0" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea v-model="form.description" rows="4" class="form-control"></textarea>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label>Bundle Note</label>
                                        <textarea v-model="form.bundle_note" rows="3" class="form-control" placeholder="Example: Includes gift wrap and a handwritten note."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-info card-outline">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h3 class="card-title">Bundle Products</h3>
                                    <button type="button" class="btn btn-sm btn-primary ml-auto" @click="addItem">
                                        <i class="fas fa-plus mr-1"></i> Add Product
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div v-for="(item, index) in form.items" :key="index" class="row align-items-end border-bottom pb-3 mb-3">
                                        <div class="col-md-8 form-group mb-md-0">
                                            <label>Product</label>
                                            <select v-model="item.product_id" class="form-control" required>
                                                <option value="">Select Product</option>
                                                <option
                                                    v-for="product in products"
                                                    :key="product.id"
                                                    :value="product.id"
                                                    :disabled="isProductDisabled(product.id, index)"
                                                >
                                                    {{ product.name }} - {{ formatMoney(product.price) }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 form-group mb-md-0">
                                            <label>Qty</label>
                                            <input v-model="item.quantity" type="number" min="1" class="form-control" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-light border btn-block" :disabled="form.items.length === 1" @click="removeItem(index)">
                                                <i class="fas fa-trash text-danger"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.items" class="text-danger text-sm">{{ form.errors.items }}</div>
                                </div>
                            </div>

                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Bundle Images</h3>
                                </div>
                                <div class="card-body">
                                    <div v-if="bundle?.images?.length" class="row mb-3">
                                        <div v-for="image in bundle.images" :key="image.id" class="col-md-2 col-4 mb-2">
                                            <img :src="image.image_path" class="img-fluid border rounded bg-white p-1" style="height: 90px; width: 100%; object-fit: contain;">
                                        </div>
                                    </div>
                                    <div class="custom-file">
                                        <input id="bundleImages" type="file" class="custom-file-input" accept="image/*" multiple @change="handleFileChange">
                                        <label class="custom-file-label" for="bundleImages">Choose images...</label>
                                    </div>
                                    <div v-if="imagePreviews.length" class="row mt-3">
                                        <div v-for="(preview, index) in imagePreviews" :key="index" class="col-md-2 col-4 mb-2">
                                            <img :src="preview" class="img-fluid border rounded bg-white p-1" style="height: 90px; width: 100%; object-fit: contain;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card card-success card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Pricing</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6 form-group">
                                            <label>Discount Type</label>
                                            <select v-model="form.discount_type" class="form-control">
                                                <option value="fixed">Fixed</option>
                                                <option value="percent">Percent</option>
                                            </select>
                                        </div>
                                        <div class="col-6 form-group">
                                            <label>Discount</label>
                                            <input v-model="form.discount_value" type="number" min="0" step="0.01" class="form-control">
                                        </div>
                                    </div>

                                    <div class="pricing-summary">
                                        <div class="d-flex justify-content-between py-2">
                                            <span>Subtotal</span>
                                            <strong>{{ formatMoney(subtotal) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between py-2">
                                            <span>Discount</span>
                                            <strong class="text-danger">-{{ formatMoney(discountAmount) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between border-top mt-2 pt-3 h5">
                                            <span>Bundle Price</span>
                                            <strong class="text-success">{{ formatMoney(finalPrice) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-warning card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Display Options</h3>
                                </div>
                                <div class="card-body">
                                    <div class="custom-control custom-switch mb-3">
                                        <input id="bundleActive" v-model="form.is_active" type="checkbox" class="custom-control-input">
                                        <label for="bundleActive" class="custom-control-label">Publish</label>
                                    </div>
                                    <div class="custom-control custom-switch mb-3">
                                        <input id="bundleFeatured" v-model="form.is_featured" type="checkbox" class="custom-control-input">
                                        <label for="bundleFeatured" class="custom-control-label">Featured</label>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input id="bundleNew" v-model="form.is_new" type="checkbox" class="custom-control-input">
                                        <label for="bundleNew" class="custom-control-label">New Arrival</label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg btn-block shadow" :disabled="form.processing || selectedProducts.size === 0">
                                <i class="fas fa-save mr-1"></i> Save Bundle
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.pricing-summary {
    background: #f8fafc;
    border: 1px solid #eef2f7;
    border-radius: 12px;
    padding: 1rem;
}
</style>
