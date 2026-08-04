<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    settings: Object,
    divisions: Array,
});

// Charges are keyed by district id (not array position) so newly added areas
// slot in without disturbing the rows already on screen.
function buildCharges() {
    const map = {};
    props.divisions.forEach((division) => {
        division.districts.forEach((district) => {
            map[district.id] = district.shipping_charge ?? '';
        });
    });
    return map;
}

const form = useForm({
    default_shipping_charge: props.settings.default_shipping_charge,
    free_shipping_threshold: props.settings.free_shipping_threshold ?? '',
    charges: buildCharges(),
});

form.transform((data) => ({
    ...data,
    charges: Object.entries(data.charges).map(([id, shipping_charge]) => ({ id, shipping_charge })),
}));

// Fold freshly created areas into the form without wiping unsaved edits.
watch(
    () => props.divisions,
    (divisions) => {
        divisions.forEach((division) => {
            division.districts.forEach((district) => {
                if (!(district.id in form.charges)) {
                    form.charges[district.id] = district.shipping_charge ?? '';
                }
            });
        });
    },
    { deep: true },
);

const search = ref('');
const bulkValue = ref({});

const visibleDivisions = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return props.divisions;

    return props.divisions
        .map((division) => ({
            ...division,
            districts: division.name.toLowerCase().includes(term)
                ? division.districts
                : division.districts.filter((d) => d.name.toLowerCase().includes(term)),
        }))
        .filter((division) => division.districts.length > 0);
});

const totalAreas = computed(() => Object.keys(form.charges).length);
const customCount = computed(
    () => Object.values(form.charges).filter((value) => value !== '' && value !== null).length,
);
const chargeError = computed(
    () => Object.entries(form.errors).find(([key]) => key.startsWith('charges.'))?.[1],
);

const formatTk = (value) => `৳${Number(value || 0).toLocaleString('en-BD')}`;

const applyToDivision = (division) => {
    const value = bulkValue.value[division.id];
    if (value === undefined || value === '') return;

    division.districts.forEach((district) => {
        form.charges[district.id] = value;
    });
    bulkValue.value[division.id] = '';
};

const clearDivision = (division) => {
    division.districts.forEach((district) => {
        form.charges[district.id] = '';
    });
};

const submit = () => {
    form.post(route('admin.shipping.update'), {
        preserveScroll: true,
    });
};

/* ---- Add a new area ---- */

const showAddArea = ref(false);
const useNewDivision = ref(false);

const areaForm = useForm({
    division_id: '',
    new_division: '',
    name: '',
    shipping_charge: '',
});

const openAddArea = () => {
    areaForm.reset();
    areaForm.clearErrors();
    useNewDivision.value = false;
    showAddArea.value = true;
};

const closeAddArea = () => {
    if (!areaForm.processing) showAddArea.value = false;
};

const submitArea = () => {
    areaForm
        .transform((data) => ({
            ...data,
            division_id: useNewDivision.value ? null : data.division_id,
            new_division: useNewDivision.value ? data.new_division : null,
        }))
        .post(route('admin.shipping.areas.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showAddArea.value = false;
            },
        });
};
</script>

<template>
    <Head title="Shipping Charges" />
    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Shipping Charges</h1>
                        <p class="text-muted mb-0 small">Set delivery charge area by area. All amounts are in Taka (৳).</p>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <form @submit.prevent="submit">
                    <div class="row">
                        <!-- Base rules -->
                        <div class="col-md-4">
                            <div class="card card-primary card-outline shadow-sm sticky-panel">
                                <div class="card-header">
                                    <h3 class="card-title">Base Rules</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Default Charge <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                            <input type="number" step="1" min="0" v-model="form.default_shipping_charge" class="form-control" :class="{ 'is-invalid': form.errors.default_shipping_charge }" required>
                                        </div>
                                        <small class="text-muted">Used for every area that has no rate of its own.</small>
                                        <div class="text-danger small mt-1" v-if="form.errors.default_shipping_charge">{{ form.errors.default_shipping_charge }}</div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label>Free Shipping Above</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                            <input type="number" step="1" min="0" v-model="form.free_shipping_threshold" class="form-control" :class="{ 'is-invalid': form.errors.free_shipping_threshold }" placeholder="Leave empty to turn off">
                                        </div>
                                        <small class="text-muted">
                                            <template v-if="form.free_shipping_threshold !== '' && form.free_shipping_threshold !== null">
                                                Orders of {{ formatTk(form.free_shipping_threshold) }} or more ship free, everywhere.
                                            </template>
                                            <template v-else>Free shipping is currently off.</template>
                                        </small>
                                        <div class="text-danger small mt-1" v-if="form.errors.free_shipping_threshold">{{ form.errors.free_shipping_threshold }}</div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white">
                                    <div class="d-flex justify-content-between small text-muted mb-3">
                                        <span>Areas with a custom rate</span>
                                        <strong class="text-dark">{{ customCount }} / {{ totalAreas }}</strong>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block shadow-sm" :disabled="form.processing">
                                        <i class="fas mr-1" :class="form.processing ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                                        {{ form.processing ? 'Saving...' : 'Save Charges' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Area wise charges -->
                        <div class="col-md-8">
                            <div class="card card-info card-outline shadow-sm">
                                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                                    <h3 class="card-title mb-0">Area Wise Charges</h3>
                                    <div class="d-flex align-items-center">
                                        <div class="area-search mr-2">
                                            <i class="fas fa-search"></i>
                                            <input type="search" v-model="search" class="form-control form-control-sm" placeholder="Search division or district...">
                                        </div>
                                        <button type="button" class="btn btn-info btn-sm add-area-btn" @click="openAddArea">
                                            <i class="fas fa-plus mr-1"></i> Add Area
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">
                                        Leave a box empty to charge the default
                                        <strong class="text-dark">{{ formatTk(form.default_shipping_charge) }}</strong> for that area.
                                    </p>
                                    <div class="alert alert-danger py-2 small" v-if="chargeError">{{ chargeError }}</div>

                                    <div v-for="division in visibleDivisions" :key="division.id" class="division-block">
                                        <div class="division-head">
                                            <h6 class="mb-0 font-weight-bold text-dark">
                                                <i class="fas fa-map-marker-alt text-info mr-1"></i> {{ division.name }}
                                                <span class="badge badge-light border ml-1">{{ division.districts.length }}</span>
                                            </h6>
                                            <div class="division-bulk">
                                                <div class="input-group input-group-sm bulk-input">
                                                    <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                                    <input type="number" min="0" v-model="bulkValue[division.id]" class="form-control" placeholder="Set all">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-info" @click="applyToDivision(division)">Apply</button>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-outline-secondary bulk-clear" title="Clear this division" @click="clearDivision(division)">
                                                    <i class="fas fa-eraser"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-4 col-md-6 mb-2" v-for="district in division.districts" :key="district.id">
                                                <label class="district-label">{{ district.name }}</label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        step="1"
                                                        v-model="form.charges[district.id]"
                                                        class="form-control"
                                                        :placeholder="String(form.default_shipping_charge || 0)"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="visibleDivisions.length === 0" class="text-center text-muted py-5">
                                        <i class="fas fa-map-signs fa-2x mb-2 d-block opacity-50"></i>
                                        No area matches "{{ search }}".
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- Add Area -->
        <Transition name="area-fade">
            <div v-if="showAddArea" class="modal d-block area-backdrop" tabindex="-1" role="dialog" @click.self="closeAddArea">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg area-card">
                        <form @submit.prevent="submitArea">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title font-weight-bold">Add New Area</h5>
                                <button type="button" class="close" aria-label="Close" @click="closeAddArea"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <label class="mb-0">Division <span class="text-danger">*</span></label>
                                        <button type="button" class="btn btn-link btn-sm p-0 toggle-link" @click="useNewDivision = !useNewDivision">
                                            {{ useNewDivision ? 'Pick an existing one' : '+ New division' }}
                                        </button>
                                    </div>

                                    <select v-if="!useNewDivision" v-model="areaForm.division_id" class="form-control mt-1" :class="{ 'is-invalid': areaForm.errors.division_id }">
                                        <option value="">Select Division</option>
                                        <option v-for="division in divisions" :key="division.id" :value="division.id">{{ division.name }}</option>
                                    </select>
                                    <input v-else type="text" v-model="areaForm.new_division" class="form-control mt-1" :class="{ 'is-invalid': areaForm.errors.new_division }" placeholder="New division name">

                                    <div class="text-danger small mt-1" v-if="areaForm.errors.division_id || areaForm.errors.new_division">
                                        {{ areaForm.errors.division_id || areaForm.errors.new_division }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Area / District Name <span class="text-danger">*</span></label>
                                    <input type="text" v-model="areaForm.name" class="form-control" :class="{ 'is-invalid': areaForm.errors.name }" placeholder="e.g. Savar" required>
                                    <div class="text-danger small mt-1" v-if="areaForm.errors.name">{{ areaForm.errors.name }}</div>
                                </div>

                                <div class="form-group mb-0">
                                    <label>Delivery Charge</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                        <input type="number" min="0" step="1" v-model="areaForm.shipping_charge" class="form-control" :class="{ 'is-invalid': areaForm.errors.shipping_charge }" :placeholder="String(form.default_shipping_charge || 0)">
                                    </div>
                                    <small class="text-muted">Leave empty to use the default {{ formatTk(form.default_shipping_charge) }}.</small>
                                    <div class="text-danger small mt-1" v-if="areaForm.errors.shipping_charge">{{ areaForm.errors.shipping_charge }}</div>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light border" :disabled="areaForm.processing" @click="closeAddArea">Cancel</button>
                                <button type="submit" class="btn btn-info shadow-sm" :disabled="areaForm.processing">
                                    <i class="fas mr-1" :class="areaForm.processing ? 'fa-spinner fa-spin' : 'fa-plus'"></i>
                                    {{ areaForm.processing ? 'Adding...' : 'Add Area' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.sticky-panel {
    position: sticky;
    top: 1rem;
}

.area-search {
    position: relative;
}

.area-search i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    font-size: 12px;
}

.area-search input {
    padding-left: 28px;
    min-width: 220px;
}

.division-block {
    padding: 12px 0 4px;
    border-top: 1px solid #f1f3f5;
}

.division-block:first-of-type {
    border-top: 0;
    padding-top: 0;
}

.division-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 10px;
}

.division-bulk {
    display: flex;
    align-items: center;
    gap: 6px;
}

.bulk-input {
    width: 190px;
}

/* AdminLayout sets a global `.btn { border-radius: 10px; padding: .6rem 1.5rem }` with
   !important. Inside an input-group that breaks the joined look and squeezes the field,
   so compact sizing is restored here. */
.input-group .btn {
    padding: 0.25rem 0.7rem !important;
    font-size: 0.8rem !important;
    font-weight: 500 !important;
    border-radius: 0 !important;
}

.input-group > .input-group-append:last-child > .btn:last-child {
    border-top-right-radius: 8px !important;
    border-bottom-right-radius: 8px !important;
}

.bulk-clear {
    padding: 0.3rem 0.6rem !important;
    border-radius: 8px !important;
    line-height: 1.5;
}

.add-area-btn {
    padding: 0.35rem 0.9rem !important;
    font-size: 0.8rem !important;
    white-space: nowrap;
}

.district-label {
    display: block;
    margin-bottom: 2px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
}

.toggle-link {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0 !important;
    border-radius: 0 !important;
}

.area-backdrop {
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(2px);
}

.area-card {
    border-radius: 12px;
    overflow: hidden;
}

.area-fade-enter-active,
.area-fade-leave-active {
    transition: opacity 0.2s ease;
}

.area-fade-enter-active .area-card,
.area-fade-leave-active .area-card {
    transition: transform 0.2s ease;
}

.area-fade-enter-from,
.area-fade-leave-to {
    opacity: 0;
}

.area-fade-enter-from .area-card,
.area-fade-leave-to .area-card {
    transform: scale(0.94);
}
</style>
