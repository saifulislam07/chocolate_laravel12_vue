<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Object
});

const activeTab = ref('general');

const tabs = [
    { id: 'general', label: 'General', icon: 'fas fa-building' },
    { id: 'branding', label: 'Branding', icon: 'fas fa-palette' },
    { id: 'payments', label: 'Payments', icon: 'fas fa-credit-card' },
    { id: 'messenger', label: 'Messenger', icon: 'fab fa-facebook-messenger' },
    { id: 'email', label: 'Email SMTP', icon: 'fas fa-envelope' },
    { id: 'maintenance', label: 'Maintenance', icon: 'fas fa-screwdriver-wrench' },
];

const form = useForm({
    site_name: props.settings?.site_name || 'Sweet Chocolate',
    email: props.settings?.email || '',
    phone: props.settings?.phone || '',
    address: props.settings?.address || '',
    maintenance_mode: props.settings?.maintenance_mode || false,
    maintenance_title: props.settings?.maintenance_title || 'We are polishing the shop',
    maintenance_message: props.settings?.maintenance_message || 'SweetChocholate is temporarily unavailable while we make a few improvements.',
    facebook_url: props.settings?.facebook_url || '',
    instagram_url: props.settings?.instagram_url || '',
    messenger_enabled: props.settings?.messenger_enabled || false,
    messenger_page_id: props.settings?.messenger_page_id || '',
    messenger_theme_color: props.settings?.messenger_theme_color || '#B99D4B',
    messenger_logged_in_greeting: props.settings?.messenger_logged_in_greeting || 'Hi! How can we help you with SweetChocholate?',
    messenger_logged_out_greeting: props.settings?.messenger_logged_out_greeting || 'Hi! Please log in to Facebook to chat with us.',
    bkash_enabled: props.settings?.bkash_enabled || false,
    bkash_mode: props.settings?.bkash_mode || 'sandbox',
    bkash_base_url: props.settings?.bkash_base_url || 'https://tokenized.sandbox.bka.sh/v1.2.0-beta',
    bkash_app_key: props.settings?.bkash_app_key || '',
    bkash_app_secret: props.settings?.bkash_app_secret || '',
    bkash_username: props.settings?.bkash_username || '',
    bkash_password: props.settings?.bkash_password || '',
    nagad_enabled: props.settings?.nagad_enabled || false,
    nagad_mode: props.settings?.nagad_mode || 'sandbox',
    nagad_base_url: props.settings?.nagad_base_url || '',
    nagad_merchant_id: props.settings?.nagad_merchant_id || '',
    nagad_merchant_number: props.settings?.nagad_merchant_number || '',
    nagad_public_key: props.settings?.nagad_public_key || '',
    nagad_private_key: props.settings?.nagad_private_key || '',
    smtp_host: props.settings?.smtp_host || '',
    smtp_port: props.settings?.smtp_port || '',
    smtp_username: props.settings?.smtp_username || '',
    smtp_password: props.settings?.smtp_password || '',
    smtp_encryption: props.settings?.smtp_encryption || 'tls',
    logo: null,
    footer_logo: null,
    favicon: null,
});

function submit() {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="System Settings" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-7">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-tools mr-2 text-primary"></i>System Settings</h1>
                        <p class="text-muted text-sm mb-0 mt-1">Settings are separated by task so the page stays easy to manage.</p>
                    </div>
                    <div class="col-sm-5 text-sm-right mt-3 mt-sm-0">
                        <button type="button" class="btn btn-success btn-lg" :disabled="form.processing" @click="submit">
                            <i class="fas fa-save mr-1"></i> Save Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card settings-nav-card">
                                <div class="card-body p-2">
                                    <button
                                        v-for="tab in tabs"
                                        :key="tab.id"
                                        type="button"
                                        class="settings-tab"
                                        :class="{ active: activeTab === tab.id }"
                                        @click="activeTab = tab.id"
                                    >
                                        <i :class="tab.icon"></i>
                                        <span>{{ tab.label }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-9">
                            <div class="card card-primary card-outline shadow-sm">
                                <div class="card-body">
                                    <div v-show="activeTab === 'general'">
                                        <h3 class="settings-title">General Information</h3>
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
                                            <textarea v-model="form.address" class="form-control" rows="4"></textarea>
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

                                    <div v-show="activeTab === 'branding'">
                                        <h3 class="settings-title">Branding Assets</h3>
                                        <div class="row">
                                            <div class="col-md-4 mb-4">
                                                <label class="d-block text-xs font-weight-bold text-muted mb-2">HEADER LOGO</label>
                                                <div class="asset-preview bg-light">
                                                    <img :src="settings?.logo || 'https://via.placeholder.com/150x50?text=No+Logo'" class="img-fluid">
                                                </div>
                                                <input type="file" @input="form.logo = $event.target.files[0]" class="form-control-file mt-3">
                                            </div>
                                            <div class="col-md-4 mb-4">
                                                <label class="d-block text-xs font-weight-bold text-muted mb-2">FOOTER LOGO</label>
                                                <div class="asset-preview bg-dark">
                                                    <img :src="settings?.footer_logo || 'https://via.placeholder.com/150x50?text=No+Logo'" class="img-fluid">
                                                </div>
                                                <input type="file" @input="form.footer_logo = $event.target.files[0]" class="form-control-file mt-3">
                                            </div>
                                            <div class="col-md-4 mb-4">
                                                <label class="d-block text-xs font-weight-bold text-muted mb-2">FAVICON</label>
                                                <div class="asset-preview bg-light">
                                                    <img :src="settings?.favicon || 'https://via.placeholder.com/32?text=F'" class="img-fluid favicon-preview">
                                                </div>
                                                <input type="file" @input="form.favicon = $event.target.files[0]" class="form-control-file mt-3">
                                            </div>
                                        </div>
                                    </div>

                                    <div v-show="activeTab === 'payments'">
                                        <h3 class="settings-title">Payment Gateways</h3>
                                        <div class="gateway-panel">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div>
                                                    <h5 class="mb-1 font-weight-bold">bKash Merchant</h5>
                                                    <p class="text-muted text-sm mb-0">Tokenized checkout API credentials.</p>
                                                </div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="bkashEnabled" v-model="form.bkash_enabled">
                                                    <label class="custom-control-label font-weight-bold" for="bkashEnabled">Enable</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 form-group">
                                                    <label>Mode</label>
                                                    <select v-model="form.bkash_mode" class="form-control">
                                                        <option value="sandbox">Sandbox</option>
                                                        <option value="live">Live</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8 form-group">
                                                    <label>Base URL</label>
                                                    <input type="text" v-model="form.bkash_base_url" class="form-control" placeholder="https://tokenized.sandbox.bka.sh/v1.2.0-beta">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 form-group">
                                                    <label>App Key</label>
                                                    <input type="text" v-model="form.bkash_app_key" class="form-control">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label>App Secret</label>
                                                    <input type="password" v-model="form.bkash_app_secret" class="form-control">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label>Username</label>
                                                    <input type="text" v-model="form.bkash_username" class="form-control">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label>Password</label>
                                                    <input type="password" v-model="form.bkash_password" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gateway-panel mt-4">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div>
                                                    <h5 class="mb-1 font-weight-bold">Nagad Merchant</h5>
                                                    <p class="text-muted text-sm mb-0">Merchant credentials and signing keys.</p>
                                                </div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="nagadEnabled" v-model="form.nagad_enabled">
                                                    <label class="custom-control-label font-weight-bold" for="nagadEnabled">Enable</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 form-group">
                                                    <label>Mode</label>
                                                    <select v-model="form.nagad_mode" class="form-control">
                                                        <option value="sandbox">Sandbox</option>
                                                        <option value="live">Live</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8 form-group">
                                                    <label>Base URL</label>
                                                    <input type="text" v-model="form.nagad_base_url" class="form-control">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label>Merchant ID</label>
                                                    <input type="text" v-model="form.nagad_merchant_id" class="form-control">
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label>Merchant Number</label>
                                                    <input type="text" v-model="form.nagad_merchant_number" class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Public Key</label>
                                                <textarea v-model="form.nagad_public_key" class="form-control" rows="3"></textarea>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label>Private Key</label>
                                                <textarea v-model="form.nagad_private_key" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-show="activeTab === 'messenger'">
                                        <h3 class="settings-title">Messenger Chat</h3>
                                        <div class="custom-control custom-switch mb-4">
                                            <input type="checkbox" class="custom-control-input" id="messengerEnabled" v-model="form.messenger_enabled">
                                            <label class="custom-control-label font-weight-bold" for="messengerEnabled">Enable Messenger Bubble</label>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8 form-group">
                                                <label>Facebook Page ID</label>
                                                <input type="text" v-model="form.messenger_page_id" class="form-control" :class="{'is-invalid': form.errors.messenger_page_id}" placeholder="Example: 123456789012345">
                                                <span class="text-danger text-sm" v-if="form.errors.messenger_page_id">{{ form.errors.messenger_page_id }}</span>
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Theme Color</label>
                                                <input type="color" v-model="form.messenger_theme_color" class="form-control p-1" :class="{'is-invalid': form.errors.messenger_theme_color}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Logged-in Greeting</label>
                                            <input type="text" v-model="form.messenger_logged_in_greeting" class="form-control">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Logged-out Greeting</label>
                                            <input type="text" v-model="form.messenger_logged_out_greeting" class="form-control">
                                        </div>
                                    </div>

                                    <div v-show="activeTab === 'email'">
                                        <h3 class="settings-title">SMTP Configuration</h3>
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
                                            <div class="col-md-8 form-group">
                                                <label>Password</label>
                                                <input type="password" v-model="form.smtp_password" class="form-control">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label>Encryption</label>
                                                <select v-model="form.smtp_encryption" class="form-control">
                                                    <option value="tls">TLS</option>
                                                    <option value="ssl">SSL</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-show="activeTab === 'maintenance'">
                                        <h3 class="settings-title">Maintenance Mode</h3>
                                        <div class="custom-control custom-switch mb-4">
                                            <input type="checkbox" class="custom-control-input" id="maintenanceMode" v-model="form.maintenance_mode">
                                            <label class="custom-control-label font-weight-bold" for="maintenanceMode">Enable Maintenance Mode</label>
                                        </div>
                                        <div class="form-group">
                                            <label>Maintenance Page Title</label>
                                            <input type="text" v-model="form.maintenance_title" class="form-control">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Maintenance Message</label>
                                            <textarea v-model="form.maintenance_message" class="form-control" rows="5"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer bg-white text-right">
                                    <button type="submit" class="btn btn-success btn-lg" :disabled="form.processing">
                                        <i class="fas fa-save mr-1"></i> {{ form.processing ? 'Saving...' : 'Save Settings' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.settings-nav-card {
    position: sticky;
    top: 88px;
}

.settings-tab {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border: 0;
    background: transparent;
    color: #64748b;
    padding: 0.85rem 1rem;
    border-radius: 12px;
    text-align: left;
    font-weight: 700;
    transition: all 0.2s ease;
}

.settings-tab:hover,
.settings-tab.active {
    background: #eef2ff;
    color: #4f46e5;
}

.settings-title {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 1.25rem;
}

.asset-preview {
    min-height: 110px;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.asset-preview img {
    max-height: 70px;
    object-fit: contain;
}

.favicon-preview {
    width: 42px;
    height: 42px;
}

.gateway-panel {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 1.25rem;
    background: #ffffff;
}
</style>
