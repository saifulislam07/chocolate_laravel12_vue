<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    roles: Array
});

function deleteRole(id) {
    if (confirm('Are you sure you want to delete this role? This might affect user access.')) {
        router.delete(route('admin.roles.destroy', id));
    }
}
</script>

<template>
    <Head title="Roles & Permissions" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Roles & Permissions</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary card-outline">
                    <div class="card-header border-0">
                        <h3 class="card-title">Role Management</h3>
                        <div class="card-tools">
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Add New Role
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th>Role Name</th>
                                    <th>Permissions</th>
                                    <th style="width: 150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="role in roles" :key="role.id">
                                    <td class="text-bold">
                                        <i class="fas fa-user-tag mr-2 text-primary"></i>
                                        {{ role.name }}
                                    </td>
                                    <td>
                                        <span v-for="permission in role.permissions" :key="permission.id" class="badge badge-secondary mr-1 mb-1">
                                            {{ permission.name }}
                                        </span>
                                        <span v-if="role.permissions.length === 0" class="text-muted italic small">No permissions assigned</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-xs mr-1">
                                            <i class="fas fa-shield-alt mr-1"></i> Edit
                                        </button>
                                        <button class="btn btn-danger btn-xs" @click="deleteRole(role.id)" v-if="role.name !== 'Super Admin'">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
