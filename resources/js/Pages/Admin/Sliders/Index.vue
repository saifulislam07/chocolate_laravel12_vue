<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    sliders: Array
});

const form = useForm({});

function deleteSlider(id) {
    if (confirm('Are you sure you want to delete this slider?')) {
        // form.delete(route('admin.sliders.destroy', id));
        alert('Delete functionality would go here');
    }
}
</script>

<template>
    <Head title="Manage Sliders" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Sliders</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Sliders</h3>
                        <div class="card-tools">
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Add New Slider
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Subtitle</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th style="width: 150px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="slider in sliders" :key="slider.id">
                                    <td>{{ slider.id }}</td>
                                    <td>
                                        <img :src="slider.image" :alt="slider.title" style="height: 40px;" class="img-thumbnail" v-if="slider.image">
                                    </td>
                                    <td v-html="slider.title"></td>
                                    <td>{{ slider.subtitle }}</td>
                                    <td>{{ slider.sort_order }}</td>
                                    <td>
                                        <span class="badge" :class="slider.is_active ? 'badge-success' : 'badge-danger'">
                                            {{ slider.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-xs mr-1">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-danger btn-xs" @click="deleteSlider(slider.id)">
                                            <i class="fas fa-trash"></i> Delete
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
