<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object
});

const form = useForm({
    site_name: props.settings?.site_name || 'Sweet Chocolate',
    email: props.settings?.email || '',
    phone: props.settings?.phone || '',
    address: props.settings?.address || '',
    facebook_url: props.settings?.facebook_url || '',
    instagram_url: props.settings?.instagram_url || '',
    smtp_host: props.settings?.smtp_host || '',
    smtp_port: props.settings?.smtp_port || '',
    smtp_username: props.settings?.smtp_username || '',
    smtp_password: props.settings?.smtp_password || '',
    smtp_encryption: props.settings?.smtp_encryption || 'tls',
    logo: null,
    favicon: null,
});

function submit() {
    form.post(route('admin.settings.update'));
}
</script>

<template>
    <Head title="System Settings" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-tools mr-2 text-primary"></i>System Settings</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card card-primary card-outline shadow-sm" style="border-radius: 12px;">
                                <div class="card-header border-0">
                                    <h3 class="card-title font-weight-bold">General Information</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Site Name</label>
                                        <input type="text" v-model="form.site_name" class="form-control" :class="{'is-invalid': form.errors.site_name}">
                                        <span class="text-danger text-sm" v-if="form.errors.site_name">{{ form.errors.site_name }}</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>Support Email</label>
                                            <input type="email" v-model="form.email" class="form-control">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Contact Phone</label>
                                            <input type="text" v-model="form.phone" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Office Address</label>
                                        <textarea v-model="form.address" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>Facebook URL</label>
                                            <input type="text" v-model="form.facebook_url" class="form-control" placeholder="https://facebook.com/yourpage">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Instagram URL</label>
                                            <input type="text" v-model="form.instagram_url" class="form-control" placeholder="https://instagram.com/yourpage">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card card-info card-outline shadow-sm" style="border-radius: 12px;">
                                <div class="card-header border-0">
                                    <h3 class="card-title font-weight-bold">SMTP Configuration</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>SMTP Host</label>
                                        <input type="text" v-model="form.smtp_host" class="form-control" placeholder="smtp.mailtrap.io">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8 form-group">
                                            <label>Username</label>
                                            <input type="text" v-model="form.smtp_username" class="form-control">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Port</label>
                                            <input type="text" v-model="form.smtp_port" class="form-control" placeholder="587">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" v-model="form.smtp_password" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Encryption</label>
                                        <select v-model="form.smtp_encryption" class="form-control">
                                            <option value="tls">TLS</option>
                                            <option value="ssl">SSL</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card card-secondary card-outline shadow-sm" style="border-radius: 12px;">
                                <div class="card-header border-0">
                                    <h3 class="card-title font-weight-bold">Visual Assets</h3>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-4">
                                        <label class="d-block text-xs font-weight-bold text-muted mb-2">SYSTEM LOGO</label>
                                        <div class="bg-light p-3 border rounded mb-2">
                                            <img :src="settings?.logo || 'https://via.placeholder.com/150x50?text=No+Logo'" class="img-fluid" style="max-height: 50px;">
                                        </div>
                                        <input type="file" @input="form.logo = $event.target.files[0]" class="form-control-file">
                                    </div>
                                    <div class="mb-0">
                                        <label class="d-block text-xs font-weight-bold text-muted mb-2">FAVICON</label>
                                        <div class="bg-light p-2 border rounded mb-2 d-inline-block">
                                            <img :src="settings?.favicon || 'https://via.placeholder.com/32?text=F'" style="width: 32px; height: 32px;">
                                        </div>
                                        <input type="file" @input="form.favicon = $event.target.files[0]" class="form-control-file">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="card p-3">
                                <button type="submit" class="btn btn-success btn-lg" :disabled="form.processing">
                                    <i class="fas fa-save mr-1"></i> Update All Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </AdminLayout>
</template>
