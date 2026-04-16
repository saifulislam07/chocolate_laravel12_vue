<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { watch } from 'vue';

const form = useForm({
    title: '',
    slug: '',
    content: '',
    is_active: true,
});

// Auto-generate slug from title
watch(() => form.title, (newTitle) => {
    form.slug = newTitle
        .toLowerCase()
        .replace(/[^\w ]+/g, '')
        .replace(/ +/g, '-');
});

const submit = () => {
    form.post(route('admin.pages.store'));
};
</script>

<template>
    <Head title="Create Page" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-plus-circle mr-2 text-primary"></i>Create New Page</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="card card-primary card-outline shadow-sm" style="border-radius: 12px;">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Page Title <span class="text-danger">*</span></label>
                                        <input type="text" v-model="form.title" class="form-control form-control-lg" :class="{'is-invalid': form.errors.title}" placeholder="Enter page title">
                                        <span class="text-danger text-sm" v-if="form.errors.title">{{ form.errors.title }}</span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Page Content <span class="text-danger">*</span></label>
                                        <textarea v-model="form.content" class="form-control" rows="15" :class="{'is-invalid': form.errors.content}" placeholder="Write your page content here (HTML supported)"></textarea>
                                        <span class="text-danger text-sm" v-if="form.errors.content">{{ form.errors.content }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card card-secondary card-outline shadow-sm" style="border-radius: 12px;">
                                <div class="card-header border-0"><h3 class="card-title font-weight-bold italic small">Page Settings</h3></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="text-xs font-weight-bold text-muted">URL SLUG</label>
                                        <input type="text" v-model="form.slug" class="form-control form-control-sm" :class="{'is-invalid': form.errors.slug}">
                                        <span class="text-danger text-xs" v-if="form.errors.slug">{{ form.errors.slug }}</span>
                                    </div>

                                    <div class="form-group custom-control custom-switch mt-4">
                                        <input type="checkbox" v-model="form.is_active" class="custom-control-input" id="statusSwitch">
                                        <label class="custom-control-label font-weight-bold" for="statusSwitch">Published Status</label>
                                    </div>

                                    <hr>
                                    <button type="submit" class="btn btn-primary btn-block btn-lg shadow-sm" :disabled="form.processing">
                                        <i class="fas fa-save mr-1"></i> Save Page
                                    </button>
                                    <Link :href="route('admin.pages.index')" class="btn btn-outline-secondary btn-block btn-sm mt-2">Cancel</Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </AdminLayout>
</template>
