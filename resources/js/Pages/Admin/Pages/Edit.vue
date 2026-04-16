<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    page: Object
});

const form = useForm({
    title: props.page.title,
    slug: props.page.slug,
    content: props.page.content,
    is_active: props.page.is_active,
});

const submit = () => {
    form.put(route('admin.pages.update', props.page.id));
};
</script>

<template>
    <Head title="Edit Page" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-edit mr-2 text-info"></i>Edit Page: {{ page.title }}</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="card card-info card-outline shadow-sm" style="border-radius: 12px;">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Page Title <span class="text-danger">*</span></label>
                                        <input type="text" v-model="form.title" class="form-control form-control-lg" :class="{'is-invalid': form.errors.title}">
                                        <span class="text-danger text-sm" v-if="form.errors.title">{{ form.errors.title }}</span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Page Content <span class="text-danger">*</span></label>
                                        <textarea v-model="form.content" class="form-control" rows="15" :class="{'is-invalid': form.errors.content}"></textarea>
                                        <span class="text-danger text-sm" v-if="form.errors.content">{{ form.errors.content }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card card-secondary card-outline shadow-sm" style="border-radius: 12px;">
                                <div class="card-header border-0"><h3 class="card-title font-weight-bold italic small">Page Management</h3></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="text-xs font-weight-bold text-muted text-uppercase">URL SLUG</label>
                                        <input type="text" v-model="form.slug" class="form-control form-control-sm" :class="{'is-invalid': form.errors.slug}">
                                        <span class="text-danger text-xs" v-if="form.errors.slug">{{ form.errors.slug }}</span>
                                    </div>

                                    <div class="form-group custom-control custom-switch mt-4">
                                        <input type="checkbox" v-model="form.is_active" class="custom-control-input" id="editStatusSwitch">
                                        <label class="custom-control-label font-weight-bold" for="editStatusSwitch">Published Status</label>
                                    </div>

                                    <hr>
                                    <button type="submit" class="btn btn-info btn-block btn-lg shadow-sm text-white font-weight-bold" :disabled="form.processing">
                                        <i class="fas fa-sync-alt mr-1"></i> Update Page
                                    </button>
                                    <Link :href="route('admin.pages.index')" class="btn btn-outline-secondary btn-block btn-sm mt-2">Back to List</Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </AdminLayout>
</template>
