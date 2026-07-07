<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    users: Array,
    roles: Array,
});

const page = usePage();
const currentUserId = computed(() => page.props.auth.user?.id);

const columns = [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'role', label: 'Role', sortable: false },
    { key: 'actions', label: 'Actions', sortable: false, width: '150px' },
];

const showModal = ref(false);
const editMode = ref(false);
const currentUserEditId = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
});

function openCreateModal() {
    editMode.value = false;
    currentUserEditId.value = null;
    form.clearErrors();
    form.reset();
    showModal.value = true;
}

function openEditModal(user) {
    editMode.value = true;
    currentUserEditId.value = user.id;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.role = user.roles?.[0]?.name || '';
    showModal.value = true;
}

function submit() {
    if (editMode.value) {
        form.put(route('admin.users.update', currentUserEditId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.users.store'), {
            onSuccess: () => closeModal(),
        });
    }
}

function closeModal() {
    showModal.value = false;
    form.reset();
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('admin.users.destroy', id));
    }
}
</script>

<template>
    <Head title="Users" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Staff Users</h1>
                        <p class="text-muted text-sm mb-0">Create staff logins and assign them a role &amp; menu access.</p>
                    </div>
                    <button @click="openCreateModal" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus mr-2"></i> Add New User
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <PremiumTable :items="users" :headers="columns" search-placeholder="Search users...">
                    <template #cell-name="{ item }">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-indigo-soft mr-3" style="width: 32px; height: 32px;">
                                <i class="fas fa-user text-indigo text-xs"></i>
                            </div>
                            <span class="font-weight-bold text-dark">{{ item.name }}</span>
                        </div>
                    </template>

                    <template #cell-role="{ item }">
                        <span v-if="item.roles?.length" class="badge badge-light border text-[10px] px-2" style="font-weight: 500; color: #4b5563;">
                            {{ item.roles[0].name }}
                        </span>
                        <span v-else class="text-muted italic text-xs">No role assigned</span>
                    </template>

                    <template #cell-actions="{ item }">
                        <div class="d-flex">
                            <button @click="openEditModal(item)" class="btn btn-light btn-sm mr-2 border shadow-none" title="Edit User">
                                <i class="fas fa-edit text-primary text-xs"></i>
                            </button>
                            <button
                                @click="deleteUser(item.id)"
                                class="btn btn-light btn-sm border shadow-none"
                                title="Delete"
                                v-if="item.id !== currentUserId && !item.roles?.some(r => r.name === 'Super Admin')"
                            >
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>

        <!-- User Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header border-bottom py-3 px-4 bg-light">
                        <h5 class="modal-title font-bold text-dark h6">
                            <i class="fas" :class="editMode ? 'fa-edit text-primary' : 'fa-plus text-success'"></i>
                            {{ editMode ? 'Edit User' : 'Create New User' }}
                        </h5>
                        <button type="button" class="close" @click="closeModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="modal-body p-4">
                            <div class="form-group mb-3">
                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-2 d-block">Full Name</label>
                                <input type="text" v-model="form.name" class="form-control border-2 text-sm" :class="{ 'is-invalid': form.errors.name }" placeholder="e.g. Jane Doe">
                                <div class="invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-2 d-block">Email</label>
                                <input type="email" v-model="form.email" class="form-control border-2 text-sm" :class="{ 'is-invalid': form.errors.email }" placeholder="e.g. jane@example.com">
                                <div class="invalid-feedback" v-if="form.errors.email">{{ form.errors.email }}</div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-2 d-block">Password <span v-if="editMode" class="normal-case font-normal">(leave blank to keep current)</span></label>
                                <input type="password" v-model="form.password" class="form-control border-2 text-sm" :class="{ 'is-invalid': form.errors.password }" placeholder="••••••••">
                                <div class="invalid-feedback" v-if="form.errors.password">{{ form.errors.password }}</div>
                            </div>
                            <div class="form-group mb-1">
                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider mb-2 d-block">Role</label>
                                <select v-model="form.role" class="form-control border-2 text-sm" :class="{ 'is-invalid': form.errors.role }">
                                    <option value="" disabled>Select a role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                                </select>
                                <div class="invalid-feedback" v-if="form.errors.role">{{ form.errors.role }}</div>
                            </div>
                        </div>
                        <div class="modal-footer border-top p-4 bg-light">
                            <button type="button" class="btn btn-light px-4 border" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm" :disabled="form.processing">
                                <i class="fas fa-save mr-2"></i> {{ editMode ? 'Update User' : 'Create User' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.modal.show { display: block; }
.icon-circle { display: flex; align-items: center; justify-content: center; border-radius: 50%; }
.bg-indigo-soft { background-color: #eef2ff; }
.text-indigo { color: #4f46e5; }
.cursor-pointer { cursor: pointer; }
</style>
