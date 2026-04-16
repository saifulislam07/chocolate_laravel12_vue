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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Dynamic Navigation</h1>
                        <p class="text-muted text-sm mb-0">Build and organize your multi-level site navigation</p>
                    </div>
                    <button class="btn btn-primary shadow-sm rounded-pill px-4 font-bold" @click="openCreateModal">
                        <i class="fas fa-plus mr-2"></i> Add Menu Item
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="premium-card bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-slate-50 border-bottom">
                                <tr>
                                    <th class="py-3 px-4 text-xs font-bold text-slate-500 text-uppercase tracking-wider" style="width: 100px">Order</th>
                                    <th class="py-3 px-4 text-xs font-bold text-slate-500 text-uppercase tracking-wider">Name</th>
                                    <th class="py-3 px-4 text-xs font-bold text-slate-500 text-uppercase tracking-wider">Target URL / Slug</th>
                                    <th class="py-3 px-4 text-xs font-bold text-slate-500 text-uppercase tracking-wider text-center">Status</th>
                                    <th class="py-3 px-4 text-xs font-bold text-slate-500 text-uppercase tracking-wider text-right pr-4" style="width: 150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.85rem;">
                                <template v-for="menu in menus" :key="menu.id">
                                    <tr class="bg-indigo-50 border-bottom">
                                        <td class="px-4"><span class="badge badge-light border px-2 py-1">#{{ menu.order }}</span></td>
                                        <td class="px-4">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-circle bg-indigo-soft mr-3" style="width: 32px; height: 32px;">
                                                    <i class="fas fa-bars text-indigo text-xs"></i>
                                                </div>
                                                <span class="font-bold text-indigo">{{ menu.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4"><code class="text-xs">{{ menu.url || '---' }}</code></td>
                                        <td class="text-center px-4">
                                            <span class="badge" :class="menu.is_active ? 'badge-success' : 'badge-danger'">{{ menu.is_active ? 'Active' : 'Inactive' }}</span>
                                        </td>
                                        <td class="text-right px-4 pr-4">
                                            <div class="d-flex justify-content-end">
                                                <button class="btn btn-light btn-sm mr-2 border shadow-none" @click="openEditModal(menu)">
                                                    <i class="fas fa-edit text-primary text-xs"></i>
                                                </button>
                                                <button class="btn btn-light btn-sm border shadow-none" @click="deleteMenu(menu.id)">
                                                    <i class="fas fa-trash text-danger text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Children -->
                                    <tr v-for="child in menu.children" :key="child.id" class="border-bottom hover-bg-slate-50">
                                        <td class="pl-5"><span class="text-muted text-xs">{{ child.order }}</span></td>
                                        <td class="pl-5">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-level-up-alt fa-rotate-90 text-slate-300 mr-3"></i>
                                                <span class="text-slate-700 font-medium">{{ child.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4"><span class="text-slate-400 text-xs">{{ child.url }}</span></td>
                                        <td class="text-center px-4">
                                            <span class="badge badge-pill border text-xs" :class="child.is_active ? 'text-success border-success bg-success-soft' : 'text-danger border-danger bg-danger-soft'">{{ child.is_active ? 'Active' : 'Inactive' }}</span>
                                        </td>
                                        <td class="text-right px-4 pr-4">
                                            <div class="d-flex justify-content-end">
                                                <button class="btn btn-link py-0 px-2 text-primary" @click="openEditModal(child)"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-link py-0 px-2 text-danger" @click="deleteMenu(child.id)"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-if="menus.length === 0">
                                    <td colspan="5" class="text-center p-5 text-slate-400">
                                        <div class="py-4">
                                            <i class="fas fa-sitemap mb-3" style="font-size: 2rem; opacity: 0.2;"></i>
                                            <p class="mb-0">No menu items defined yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
