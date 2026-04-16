<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    menus: Array,
    allMenus: Array
});

const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: '',
    url: '',
    parent_id: '',
    order: 0,
    is_active: true,
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (menu) => {
    isEditing.value = true;
    form.id = menu.id;
    form.name = menu.name;
    form.url = menu.url;
    form.parent_id = menu.parent_id || '';
    form.order = menu.order;
    form.is_active = menu.is_active;
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.menus.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.menus.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteMenu = (id) => {
    if (confirm('Are you sure? This will delete the menu item and its children.')) {
        form.delete(route('admin.menus.destroy', id));
    }
};
</script>

<template>
    <Head title="Navigation Menu Management" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-list mr-2 text-info"></i>Dynamic Navigation</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-info card-outline shadow-sm" style="border-radius: 12px;">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">Site Menu Builder</h3>
                        <div class="card-tools">
                            <button class="btn btn-info btn-sm rounded-pill px-3 shadow-sm text-white font-weight-bold" @click="openCreateModal">
                                <i class="fas fa-plus mr-1"></i> Add Menu Item
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Order</th>
                                        <th>Name</th>
                                        <th>Target URL / Slug</th>
                                        <th class="text-center">Status</th>
                                        <th style="width: 150px" class="text-right pr-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="menu in menus" :key="menu.id">
                                        <tr class="bg-light-blue">
                                            <td style="width: 80px"><code>#{{ menu.order }}</code></td>
                                            <td class="font-weight-bold text-primary">{{ menu.name }}</td>
                                            <td><span class="text-muted">{{ menu.url || '---' }}</span></td>
                                            <td class="text-center">
                                                <span class="badge" :class="menu.is_active ? 'badge-success' : 'badge-danger'">{{ menu.is_active ? 'Active' : 'Inactive' }}</span>
                                            </td>
                                            <td class="text-right pr-4">
                                                <button class="btn btn-outline-info btn-xs mr-1 p-1 px-2" @click="openEditModal(menu)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-xs p-1 px-2" @click="deleteMenu(menu.id)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Children -->
                                        <tr v-for="child in menu.children" :key="child.id">
                                            <td class="pl-4"><code>- {{ child.order }}</code></td>
                                            <td class="pl-4"><i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-2"></i> {{ child.name }}</td>
                                            <td><span class="text-muted small">{{ child.url }}</span></td>
                                            <td class="text-center">
                                                <span class="badge badge-pill border" :class="child.is_active ? 'text-success border-success' : 'text-danger border-danger'">{{ child.is_active ? 'Active' : 'Inactive' }}</span>
                                            </td>
                                            <td class="text-right pr-4">
                                                <button class="btn btn-link btn-xs mr-1 text-info" @click="openEditModal(child)"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-link btn-xs text-danger" @click="deleteMenu(child.id)"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr v-if="menus.length === 0">
                                        <td colspan="5" class="text-center p-5 text-muted h5">No menu items defined yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Menu Modal -->
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5);" v-if="showModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header bg-info text-white" style="border-radius: 12px 12px 0 0;">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas mr-2" :class="isEditing ? 'fa-edit' : 'fa-plus'"></i>
                            {{ isEditing ? 'Edit Menu Item' : 'Create Menu Item' }}
                        </h5>
                        <button type="button" class="close text-white" @click="closeModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submitForm">
                        <div class="modal-body bg-light">
                            <div class="form-group">
                                <label class="font-weight-bold">Menu Text <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" v-model="form.name" required placeholder="e.g. Products">
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Target URL / Page Slug</label>
                                <input type="text" class="form-control" v-model="form.url" placeholder="e.g. /products or about-us">
                                <small class="text-muted italic">Leave empty for category parent. If linking to a Page, just enter the slug.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Parent Item</label>
                                    <select class="form-control" v-model="form.parent_id">
                                        <option value="">-- Main Menu --</option>
                                        <option v-for="m in allMenus.filter(x => !x.parent_id && x.id !== form.id)" :key="m.id" :value="m.id">{{ m.name }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Display Order</label>
                                    <input type="number" class="form-control" v-model="form.order">
                                </div>
                            </div>

                            <div class="form-group custom-control custom-switch mt-2">
                                <input type="checkbox" v-model="form.is_active" class="custom-control-input" id="menuStatus">
                                <label class="custom-control-label font-weight-bold" for="menuStatus">Active Status</label>
                            </div>
                        </div>
                        <div class="modal-footer bg-white border-0" style="border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-secondary px-4 shadow-sm" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-info px-4 shadow-sm text-white font-weight-bold" :disabled="form.processing">
                                <i class="fas fa-save mr-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
