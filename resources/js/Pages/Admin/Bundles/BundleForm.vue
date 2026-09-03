<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
// Must stay in sync with the `images.*` rule in BundleController::validatedPayload().
const MAX_IMAGE_MB = 2;
const ACCEPTED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const rejectedImages = ref([]);
const fileInput = ref(null);

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

    // Catch oversized/unsupported files here so the server does not reject the
    // whole submit with an error the form has no room to show.
    const rejected = files.filter(
        (file) => file.size > MAX_IMAGE_MB * 1024 * 1024 || !ACCEPTED_IMAGE_TYPES.includes(file.type)
    );
    rejectedImages.value = rejected.map((file) => `${file.name} (${(file.size / 1048576).toFixed(1)} MB)`);

    const accepted = files.filter((file) => !rejected.includes(file));

    form.images = accepted;
    // Readers resolve out of order, so pre-size the array and fill by index —
    // the previews have to stay aligned with form.images to be removable.
    imagePreviews.value = accepted.map(() => null);

    accepted.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => { imagePreviews.value[index] = e.target.result; };
        reader.readAsDataURL(file);
    });
}

function removeSelectedImage(index) {
    form.images = form.images.filter((_, i) => i !== index);
    imagePreviews.value.splice(index, 1);
    // A FileList is read-only, so reset the input to allow re-picking the same file.
    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function deleteExistingImage(image) {
    if (!confirm('Delete this image? This cannot be undone.')) return;

    router.delete(route('admin.bundles.images.destroy', [props.bundle.id, image.id]), { preserveScroll: true });
}

function setPrimaryImage(imageId) {
    router.patch(route('admin.bundles.images.primary', [props.bundle.id, imageId]), {}, { preserveScroll: true });
}

// Laravel keys nested rules as `images.0` / `items.1.product_id`, so a plain
// per-field lookup misses them and the submit fails with nothing on screen.
const errorMessages = computed(() => Object.values(form.errors).filter(Boolean));
const imageError = computed(() => Object.entries(form.errors).find(([key]) => key.startsWith('images'))?.[1]);

function itemError(index) {
    return form.errors[`items.${index}.product_id`] || form.errors[`items.${index}.quantity`];
}

function submit() {
    form.post(props.submitRoute, {
        forceFormData: true,
        onError: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
    });
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
                    <div v-if="errorMessages.length" class="alert alert-danger">
                        <h6 class="font-weight-bold mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> Bundle could not be saved</h6>
                        <ul class="mb-0 pl-3">
                            <li v-for="(message, index) in errorMessages" :key="index">{{ message }}</li>
                        </ul>
                    </div>

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
                                        <RichTextEditor v-model="form.description" :height="180" :invalid="!!form.errors.description" placeholder="Describe this bundle..." />
                                    </div>

                                    <div class="form-group mb-0">
                                        <label>Bundle Note</label>
                                        <RichTextEditor v-model="form.bundle_note" :height="130" toolbar="basic" :invalid="!!form.errors.bundle_note" placeholder="Example: Includes gift wrap and a handwritten note." />
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
                                        <div v-if="itemError(index)" class="col-12 text-danger text-sm mt-2">{{ itemError(index) }}</div>
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
                                        <div v-for="image in bundle.images" :key="image.id" class="col-md-3 col-6 mb-3">
                                            <div class="gallery-tile position-relative border rounded bg-white p-1">
                                                <img :src="image.image_path" class="img-fluid rounded" style="height: 90px; width: 100%; object-fit: contain;">
                                                <span v-if="image.is_primary" class="badge badge-success position-absolute" style="top: 5px; left: 5px;">Main</span>
                                                <button type="button" class="btn btn-danger btn-sm tile-remove position-absolute" style="top: 5px; right: 5px;" title="Delete image" @click="deleteExistingImage(image)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button v-if="!image.is_primary" type="button" class="btn btn-outline-success btn-sm btn-block mt-1" @click="setPrimaryImage(image.id)">
                                                    <i class="fas fa-star mr-1"></i> Set as Main
                                                </button>
                                                <div v-else class="text-muted text-center small mt-1">Current main image</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="custom-file">
                                        <input id="bundleImages" ref="fileInput" type="file" class="custom-file-input" accept=".jpg,.jpeg,.png,.webp" multiple @change="handleFileChange">
                                        <label class="custom-file-label" for="bundleImages">Choose images...</label>
                                    </div>
                                    <small class="form-text text-muted">JPG, PNG or WEBP &middot; up to {{ MAX_IMAGE_MB }} MB each.</small>
                                    <div v-if="rejectedImages.length" class="text-danger text-sm mt-2">
                                        Skipped (over {{ MAX_IMAGE_MB }} MB or unsupported format): {{ rejectedImages.join(', ') }}
                                    </div>
                                    <div v-if="imageError" class="text-danger text-sm mt-2">{{ imageError }}</div>
                                    <div v-if="imagePreviews.length" class="row mt-3">
                                        <div class="col-12 mb-2"><strong class="text-success small">New selection (not saved yet)</strong></div>
                                        <div v-for="(preview, index) in imagePreviews" :key="index" class="col-md-3 col-6 mb-3">
                                            <div class="gallery-tile position-relative border rounded bg-white p-1">
                                                <img :src="preview" class="img-fluid rounded" style="height: 90px; width: 100%; object-fit: contain;">
                                                <button type="button" class="btn btn-danger btn-sm tile-remove position-absolute" style="top: 5px; right: 5px;" title="Remove from selection" @click="removeSelectedImage(index)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
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
                                        <div class="col-12 col-md-6 form-group">
                                            <label>Discount Type</label>
                                            <select v-model="form.discount_type" class="form-control">
                                                <option value="fixed">Fixed</option>
                                                <option value="percent">Percent</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6 form-group">
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
.tile-remove {
    line-height: 1;
    padding: 0.15rem 0.4rem;
}

.pricing-summary {
    background: #f8fafc;
    border: 1px solid #eef2f7;
    border-radius: 12px;
    padding: 1rem;
}
</style>
