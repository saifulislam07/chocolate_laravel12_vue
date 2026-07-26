<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    messages: Array
});

const columns = [
    { key: 'index', label: '#', sortable: true, width: '60px' },
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'subject', label: 'Subject', sortable: false },
    { key: 'created_at', label: 'Date', sortable: true, width: '140px' },
    { key: 'is_read', label: 'Status', sortable: true, width: '110px' },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' }
];

const showModal = ref(false);
const activeMessage = ref(null);

function viewMessage(message) {
    activeMessage.value = message;
    showModal.value = true;
    if (!message.is_read) {
        router.patch(route('admin.contact-messages.read', message.id), {}, { preserveScroll: true, preserveState: true });
    }
}

function closeModal() {
    showModal.value = false;
    activeMessage.value = null;
}

function deleteMessage(id) {
    if (confirm('Are you sure you want to delete this message?')) {
        router.delete(route('admin.contact-messages.destroy', id));
    }
}
</script>

<template>
    <Head title="Contact Messages" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3 text-slate-800">Contact Messages</h1>
                        <p class="text-muted text-sm mb-0">Messages submitted through the site's Contact Us form</p>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <PremiumTable
                    :items="messages"
                    :headers="columns"
                    search-placeholder="Search messages..."
                >
                    <template #cell-index="{ index }">
                        <span class="text-muted">{{ index + 1 }}</span>
                    </template>

                    <template #cell-name="{ item }">
                        <div>
                            <div class="font-weight-bold text-dark">{{ item.name }}</div>
                            <div class="text-[10px] text-muted tracking-tight mt-1" v-if="item.phone">{{ item.phone }}</div>
                        </div>
                    </template>

                    <template #cell-subject="{ item }">
                        <div class="text-muted truncate" style="max-width: 300px;">{{ item.subject || '—' }}</div>
                    </template>

                    <template #cell-created_at="{ item }">
                        <span class="text-xs text-muted">{{ new Date(item.created_at).toLocaleDateString() }}</span>
                    </template>

                    <template #cell-is_read="{ item }">
                        <span class="badge" :class="item.is_read ? 'badge-secondary' : 'badge-success'">
                            {{ item.is_read ? 'Read' : 'New' }}
                        </span>
                    </template>

                    <template #cell-actions="{ item }">
                        <div class="d-flex">
                            <button @click="viewMessage(item)" class="btn btn-light btn-sm mr-2 border shadow-none" title="View">
                                <i class="fas fa-eye text-primary text-xs"></i>
                            </button>
                            <button @click="deleteMessage(item.id)" class="btn btn-light btn-sm border shadow-none" title="Delete">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>

        <!-- Message Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom py-3 px-4 bg-light">
                        <h5 class="modal-title font-bold text-dark h6">
                            <i class="fas fa-envelope-open-text mr-2 text-primary"></i>
                            {{ activeMessage?.subject || 'Contact Message' }}
                        </h5>
                        <button type="button" class="close" @click="closeModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4" v-if="activeMessage">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider d-block">From</label>
                                <div class="text-dark">{{ activeMessage.name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider d-block">Email</label>
                                <a :href="`mailto:${activeMessage.email}`" class="text-dark">{{ activeMessage.email }}</a>
                            </div>
                        </div>
                        <div class="row mb-3" v-if="activeMessage.phone">
                            <div class="col-md-6">
                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider d-block">Phone</label>
                                <div class="text-dark">{{ activeMessage.phone }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-xs font-bold text-muted text-uppercase tracking-wider d-block">Received</label>
                                <div class="text-dark">{{ new Date(activeMessage.created_at).toLocaleString() }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-muted text-uppercase tracking-wider d-block mb-2">Message</label>
                            <p class="text-dark" style="white-space: pre-wrap;">{{ activeMessage.message }}</p>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-3 px-4">
                        <button type="button" class="btn btn-light border" @click="closeModal">Close</button>
                        <a v-if="activeMessage" :href="`mailto:${activeMessage.email}`" class="btn btn-primary">
                            <i class="fas fa-reply mr-1"></i> Reply by Email
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
