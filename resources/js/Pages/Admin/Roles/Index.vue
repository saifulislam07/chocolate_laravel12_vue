<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    roles: Array,
    permissions: Array
});

const columns = [
    { key: 'name', label: 'Role Name', sortable: true },
    { key: 'permissions', label: 'Assigned Permissions', sortable: false },
    { key: 'actions', label: 'Actions', sortable: false, width: '150px' }
];

const showModal = ref(false);
const editMode = ref(false);
const currentRoleId = ref(null);

const form = useForm({
    name: '',
    permissions: []
});

// Group permissions by module
const groupedPermissions = computed(() => {
    const groups = {};
    props.permissions.forEach(permission => {
        const parts = permission.name.split('_');
        let module = parts.length > 1 ? parts.slice(1).join(' ') : 'System';
        
        // Special mapping for common names
        const moduleMap = {
            'products': 'Product Management',
            'categories': 'Categories & Taxonomy',
            'brands': 'Brand Management',
            'sales': 'Sales & Orders',
            'pos': 'Point of Sale',
            'expenses': 'Expense Tracking',
            'settings': 'System Settings'
        };
        
        module = moduleMap[module.toLowerCase()] || module.charAt(0).toUpperCase() + module.slice(1);

        if (!groups[module]) groups[module] = [];
        groups[module].push(permission);
    });
    return groups;
});

function openCreateModal() {
    editMode.value = false;
    currentRoleId.value = null;
    form.clearErrors();
    form.reset();
    showModal.value = true;
}

function openEditModal(role) {
    editMode.value = true;
    currentRoleId.value = role.id;
    form.clearErrors();
    form.name = role.name;
    form.permissions = role.permissions.map(p => p.name);
    showModal.value = true;
}

function submit() {
    if (editMode.value) {
        form.put(route('admin.roles.update', currentRoleId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.roles.store'), {
            onSuccess: () => closeModal(),
        });
    }
}

function closeModal() {
    showModal.value = false;
    form.reset();
}

function deleteRole(id) {
    if (confirm('Are you sure you want to delete this role? This might affect user access.')) {
        router.delete(route('admin.roles.destroy', id));
    }
}

function togglePermission(name) {
    const index = form.permissions.indexOf(name);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(name);
    }
}
</script>

<template>
    <Head title="Roles & Permissions" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Roles & Permissions</h1>
                        <p class="text-muted text-sm mb-0">Manage user access levels and system permissions</p>
                    </div>
                    <button @click="openCreateModal" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus mr-2"></i> Add New Role
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <PremiumTable 
                    :items="roles" 
                    :headers="columns"
                    search-placeholder="Search roles..."
                >
                    <!-- Role Name Cell -->
                    <template #cell-name="{ item }">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-indigo-soft mr-3" style="width: 32px; height: 32px;">
                                <i class="fas fa-user-tag text-indigo text-xs"></i>
                            </div>
                            <span class="font-weight-bold text-dark">{{ item.name }}</span>
                        </div>
                    </template>

                    <!-- Permissions Cell -->
                    <template #cell-permissions="{ item }">
                        <div class="d-flex flex-wrap gap-1">
                            <span v-for="permission in item.permissions.slice(0, 8)" :key="permission.id" 
                                  class="badge badge-light border text-[10px] mr-1 mb-1 px-2" 
                                  style="font-weight: 500; color: #4b5563;">
                                {{ permission.name.replace(/_/g, ' ') }}
                            </span>
                            <span v-if="item.permissions.length > 8" class="badge badge-primary-soft text-[10px] px-2">
                                +{{ item.permissions.length - 8 }} more
                            </span>
                            <span v-if="item.permissions.length === 0" class="text-muted italic text-xs">No permissions assigned</span>
                        </div>
                    </template>

                    <!-- Actions Cell -->
                    <template #cell-actions="{ item }">
                        <div class="d-flex">
                            <button @click="openEditModal(item)" class="btn btn-light btn-sm mr-2 border shadow-none" title="Edit Permissions">
                                <i class="fas fa-shield-alt text-primary text-xs"></i>
                            </button>
                            <button @click="deleteRole(item.id)" class="btn btn-light btn-sm border shadow-none" title="Delete" v-if="item.name !== 'Super Admin' && item.name !== 'Administrator'">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>

        <!-- Role Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header border-bottom py-3 px-4 bg-light">
                        <h5 class="modal-title font-bold text-dark h6">
                            <i class="fas" :class="editMode ? 'fa-edit text-primary' : 'fa-plus text-success'"></i> 
                            {{ editMode ? 'Edit Role: ' + form.name : 'Create New Role' }}
                        </h5>
                        <button type="button" class="close" @click="closeModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                            <!-- Role Name -->
                            <div class="form-group mb-4">
                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-2 d-block">Role Name</label>
                                <input 
                                    type="text" 
                                    v-model="form.name" 
                                    class="form-control form-control-lg border-2 text-sm" 
                                    :class="{'is-invalid': form.errors.name}"
                                    placeholder="Enter role name (e.g. Sales Manager)"
                                    :disabled="editMode && (form.name === 'Super Admin' || form.name === 'Administrator')"
                                >
                                <div class="invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</div>
                            </div>

                            <!-- Permissions Section -->
                            <div class="permissions-container mt-4">
                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-3 d-block">Module Permissions</label>
                                
                                <div v-for="(perms, module) in groupedPermissions" :key="module" class="mb-4 bg-light-gray p-3 rounded-lg border">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-xs font-bold text-dark mb-0">{{ module }}</h6>
                                        <div class="text-[10px] text-muted">{{ perms.filter(p => form.permissions.includes(p.name)).length }} / {{ perms.length }} Selected</div>
                                    </div>
                                    <div class="row no-gutters">
                                        <div v-for="permission in perms" :key="permission.id" class="col-md-6 col-lg-4 mb-2">
                                            <div 
                                                class="permission-item d-flex align-items-center p-2 rounded cursor-pointer transition-all"
                                                :class="form.permissions.includes(permission.name) ? 'bg-white shadow-sm border-primary' : 'hover-bg-gray'"
                                                @click="togglePermission(permission.name)"
                                                style="border: 1px solid transparent; min-height: 42px;"
                                            >
                                                <div class="custom-control custom-checkbox mr-2 pt-1">
                                                    <input 
                                                        type="checkbox" 
                                                        class="custom-control-input" 
                                                        :id="'p-' + permission.id"
                                                        :checked="form.permissions.includes(permission.name)"
                                                    >
                                                    <label class="custom-control-label" :for="'p-' + permission.id"></label>
                                                </div>
                                                <span class="text-xs font-medium text-capitalize text-slate-700 select-none">
                                                    {{ permission.name.split('_')[0] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top p-4 bg-light">
                            <button type="button" class="btn btn-light px-4 border" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm" :disabled="form.processing">
                                <i class="fas fa-save mr-2"></i> {{ editMode ? 'Update Role' : 'Create Role' }}
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

.bg-light-gray {
    background-color: #f8fafc;
}

.icon-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.bg-indigo-soft {
    background-color: #eef2ff;
}

.text-indigo {
    color: #4f46e5;
}

.permission-item:hover {
    background-color: rgba(255,255,255,0.8);
}

.permission-item.border-primary {
    border-color: #6366f1 !important;
}

.hover-bg-gray:hover {
    background-color: #f1f5f9;
}

.cursor-pointer {
    cursor: pointer;
}

.badge-primary-soft {
    background-color: #eef2ff;
    color: #4f46e5;
}

/* Custom Checkbox Alignment */
.custom-control {
    padding-left: 1.5rem;
    min-height: 1.5rem;
}

.custom-control-label::before, 
.custom-control-label::after {
    top: 0.15rem;
    width: 1.1rem;
    height: 1.1rem;
}

/* Scrollbar styling */
.modal-body::-webkit-scrollbar {
    width: 6px;
}
.modal-body::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.modal-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.modal-body::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
