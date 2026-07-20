<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    testimonials: Array
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '60px' },
    { key: 'customer_name', label: 'Customer', sortable: true },
    { key: 'quote', label: 'Quote', sortable: false },
    { key: 'sort_order', label: 'Order', sortable: true, width: '90px' },
    { key: 'is_active', label: 'Status', sortable: true, width: '110px' },
    { key: 'actions', label: 'Actions', sortable: false, width: '150px' }
];

const showModal = ref(false);
const editMode = ref(false);
const currentId = ref(null);

const form = useForm({
    customer_name: '',
    location: '',
    quote: '',
    rating: null,
    sort_order: 0,
    is_active: true,
});

function openCreateModal() {
    editMode.value = false;
    currentId.value = null;
    form.clearErrors();
    form.reset();
    showModal.value = true;
}

function openEditModal(testimonial) {
    editMode.value = true;
    currentId.value = testimonial.id;
    form.clearErrors();
    form.customer_name = testimonial.customer_name;
    form.location = testimonial.location;
    form.quote = testimonial.quote;
    form.rating = testimonial.rating;
    form.sort_order = testimonial.sort_order;
    form.is_active = !!testimonial.is_active;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
}

function submit() {
    if (editMode.value) {
        form.put(route('admin.testimonials.update', currentId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.testimonials.store'), {
            onSuccess: () => closeModal(),
        });
    }
}

function deleteTestimonial(id) {
    if (confirm('Are you sure you want to delete this testimonial?')) {
        router.delete(route('admin.testimonials.destroy', id));
    }
}
</script>

<template>
    <Head title="Manage Testimonials" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3 text-slate-800">Testimonials</h1>
                        <p class="text-muted text-sm mb-0">Manage the guest quotes shown on the homepage</p>
                    </div>
                    <button @click="openCreateModal" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus mr-2"></i> Add New Testimonial
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <PremiumTable
                    :items="testimonials"
                    :headers="columns"
                    search-placeholder="Search testimonials..."
                >
                    <template #cell-index="{ index }">
                        <span class="text-muted">{{ index + 1 }}</span>
                    </template>

                    <template #cell-customer_name="{ item }">
                        <div>
                            <div class="font-weight-bold text-dark">{{ item.customer_name }}</div>
                            <div class="text-[10px] text-muted tracking-tight mt-1">{{ item.location }}</div>
                        </div>
                    </template>

                    <template #cell-quote="{ item }">
                        <div class="text-muted truncate" style="max-width: 380px;">&ldquo;{{ item.quote }}&rdquo;</div>
                    </template>

                    <template #cell-is_active="{ item }">
                        <span class="badge" :class="item.is_active ? 'badge-success' : 'badge-danger'">
                            {{ item.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </template>

                    <template #cell-actions="{ item }">
                        <div class="d-flex">
                            <button @click="openEditModal(item)" class="btn btn-light btn-sm mr-2 border shadow-none" title="Edit">
                                <i class="fas fa-edit text-primary text-xs"></i>
                            </button>
                            <button @click="deleteTestimonial(item.id)" class="btn btn-light btn-sm border shadow-none" title="Delete">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>

        <!-- Testimonial Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom py-3 px-4 bg-light">
                        <h5 class="modal-title font-bold text-dark h6">
                            <i class="fas mr-2" :class="editMode ? 'fa-edit text-primary' : 'fa-plus text-success'"></i>
                            {{ editMode ? 'Modify Testimonial' : 'Add New Testimonial' }}
                        </h5>
                        <button type="button" class="close" @click="closeModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="modal-body p-4">
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="text-xs font-bold text-muted text-uppercase tracking-wider">Customer Name</label>
                                    <input type="text" v-model="form.customer_name" class="form-control border-2" placeholder="e.g. Jannatul Khatun" required>
                                    <div class="text-danger text-xs mt-1" v-if="form.errors.customer_name">{{ form.errors.customer_name }}</div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="text-xs font-bold text-muted text-uppercase tracking-wider">Location</label>
                                    <input type="text" v-model="form.location" class="form-control" placeholder="e.g. Mohammadpur, Dhaka">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider">Quote</label>
                                <textarea v-model="form.quote" class="form-control" rows="4" placeholder="What did the guest say?" required></textarea>
                                <div class="text-danger text-xs mt-1" v-if="form.errors.quote">{{ form.errors.quote }}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 form-group mb-0">
                                    <label class="text-xs font-bold text-muted text-uppercase tracking-wider">Rating (1-5)</label>
                                    <input type="number" v-model="form.rating" min="1" max="5" class="form-control">
                                </div>
                                <div class="col-md-4 form-group mb-0">
                                    <label class="text-xs font-bold text-muted text-uppercase tracking-wider">Sort Order</label>
                                    <input type="number" v-model="form.sort_order" class="form-control">
                                </div>
                                <div class="col-md-4 form-group pt-4">
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="checkbox" v-model="form.is_active" class="custom-control-input" id="testimonialStatus">
                                        <label class="custom-control-label font-bold text-sm" for="testimonialStatus">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top p-4 bg-light">
                            <button type="button" class="btn btn-light px-4 border" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm" :disabled="form.processing">
                                <i class="fas fa-save mr-2"></i> {{ editMode ? 'Update Testimonial' : 'Publish Testimonial' }}
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

.cursor-pointer { cursor: pointer; }

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
