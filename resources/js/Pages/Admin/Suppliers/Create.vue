<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    company_name: '',
    phone: '',
    email: '',
    address: '',
});

function submit() {
    form.post(route('admin.suppliers.store'));
}
</script>

<template>
    <Head title="Add Supplier" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Add Supplier</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">New Supplier Information</h3>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="name">Supplier Name <span class="text-danger">*</span></label>
                                    <input type="text" v-model="form.name" class="form-control" id="name" placeholder="Enter name" required>
                                    <div v-if="form.errors.name" class="text-danger text-sm">{{ form.errors.name }}</div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="company_name">Company Name</label>
                                    <input type="text" v-model="form.company_name" class="form-control" id="company_name" placeholder="Enter company name">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="phone">Phone <span class="text-danger">*</span></label>
                                    <input type="text" v-model="form.phone" class="form-control" id="phone" placeholder="Enter phone" required>
                                    <div v-if="form.errors.phone" class="text-danger text-sm">{{ form.errors.phone }}</div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="email">Email</label>
                                    <input type="email" v-model="form.email" class="form-control" id="email" placeholder="Enter email">
                                    <div v-if="form.errors.email" class="text-danger text-sm">{{ form.errors.email }}</div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea v-model="form.address" class="form-control" id="address" rows="3" placeholder="Enter address"></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">Save Supplier</button>
                            <Link :href="route('admin.suppliers.index')" class="btn btn-default ml-2">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
