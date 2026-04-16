<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PremiumTable from '@/Components/PremiumTable.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    expenses: Array,
    categories: Array
});

const columns = [
    { key: 'expense_date', label: 'Date', sortable: true },
    { key: 'category', label: 'Category', sortable: true },
    { key: 'reference_no', label: 'Reference', sortable: true },
    { key: 'amount', label: 'Amount', sortable: true, cellClass: 'text-right' },
    { key: 'description', label: 'Description', sortable: false },
    { key: 'actions', label: 'Actions', sortable: false, width: '120px' }
];

const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    expense_category_id: '',
    amount: '',
    expense_date: new Date().toISOString().slice(0, 10),
    description: '',
    reference_no: '',
});

const totalExpensesValue = computed(() => {
    return props.expenses.reduce((sum, item) => sum + parseFloat(item.amount), 0).toFixed(2);
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (expense) => {
    isEditing.value = true;
    form.id = expense.id;
    form.expense_category_id = expense.expense_category_id;
    form.amount = expense.amount;
    form.expense_date = expense.expense_date;
    form.description = expense.description;
    form.reference_no = expense.reference_no;
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.expenses.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.expenses.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteExpense = (id) => {
    if (confirm('Are you sure you want to delete this expense record?')) {
        form.delete(route('admin.expenses.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Expense Management" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Expenses</h1>
                        <p class="text-muted text-sm mb-0">Track and manage your business expenditures</p>
                    </div>
                    <div class="d-flex">
                        <Link :href="route('admin.expense-categories.index')" class="btn btn-light border mr-2 shadow-sm">
                            <i class="fas fa-tags mr-2 text-danger"></i> Categories
                        </Link>
                        <button class="btn btn-danger shadow-sm" @click="openCreateModal">
                            <i class="fas fa-plus mr-2"></i> Record Expense
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="premium-stat-card bg-white p-3 rounded-lg shadow-sm border border-danger-soft">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-danger-soft mr-3">
                                    <i class="fas fa-wallet text-danger"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-uppercase font-bold text-muted">Total Expense</div>
                                    <div class="h4 font-bold mb-0 text-danger">৳{{ totalExpensesValue }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <PremiumTable 
                    :items="expenses" 
                    :headers="columns"
                    search-placeholder="Search expenses by reference or description..."
                >
                    <!-- Date Cell -->
                    <template #cell-expense_date="{ item }">
                        <div class="text-sm font-medium">{{ item.expense_date }}</div>
                    </template>

                    <!-- Category Cell -->
                    <template #cell-category="{ item }">
                        <span class="badge badge-secondary">{{ item.category?.name }}</span>
                    </template>

                    <!-- Reference Cell -->
                    <template #cell-reference_no="{ item }">
                        <code class="text-xs">{{ item.reference_no || 'N/A' }}</code>
                    </template>

                    <!-- Amount Cell -->
                    <template #cell-amount="{ item }">
                        <div class="font-weight-bold text-danger">৳{{ item.amount }}</div>
                    </template>

                    <!-- Description Cell -->
                    <template #cell-description="{ item }">
                        <div class="text-xs text-muted text-truncate" style="max-width: 250px;" :title="item.description">
                            {{ item.description || '-' }}
                        </div>
                    </template>

                    <!-- Actions Cell -->
                    <template #cell-actions="{ item }">
                        <div class="d-flex">
                            <button @click="openEditModal(item)" class="btn btn-light btn-sm mr-2 border shadow-none">
                                <i class="fas fa-edit text-primary text-xs"></i>
                            </button>
                            <button @click="deleteExpense(item.id)" class="btn btn-light btn-sm border shadow-none">
                                <i class="fas fa-trash text-danger text-xs"></i>
                            </button>
                        </div>
                    </template>
                </PremiumTable>
            </div>
        </section>

        <!-- Expense Modal -->
        <div class="modal fade show" tabindex="-1" role="dialog" style="display: block; background: rgba(0,0,0,0.5);" v-if="showModal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas mr-2" :class="isEditing ? 'fa-edit' : 'fa-plus'"></i>
                            {{ isEditing ? 'Edit Expense Record' : 'Record New Expense' }}
                        </h5>
                        <button type="button" class="close text-white" @click="closeModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="submitForm">
                        <div class="modal-body bg-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Category <span class="text-danger">*</span></label>
                                        <select class="form-control" v-model="form.expense_category_id" :class="{ 'is-invalid': form.errors.expense_category_id }" required>
                                            <option value="">Select Category</option>
                                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                        </select>
                                        <span class="error invalid-feedback" v-if="form.errors.expense_category_id">{{ form.errors.expense_category_id }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" v-model="form.expense_date" :class="{ 'is-invalid': form.errors.expense_date }" required>
                                        <span class="error invalid-feedback" v-if="form.errors.expense_date">{{ form.errors.expense_date }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                            <input type="number" step="0.01" class="form-control font-weight-bold" v-model="form.amount" :class="{ 'is-invalid': form.errors.amount }" required placeholder="0.00">
                                        </div>
                                        <span class="text-danger text-sm" v-if="form.errors.amount">{{ form.errors.amount }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Reference No</label>
                                        <input type="text" class="form-control" v-model="form.reference_no" placeholder="e.g. INV-12345">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Description / Remarks</label>
                                <textarea class="form-control" v-model="form.description" rows="3" placeholder="Enter expense details..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-white border-0" style="border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-secondary px-4 shadow-sm" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-danger px-4 shadow-sm" :disabled="form.processing">
                                <i class="fas fa-save mr-1"></i> {{ isEditing ? 'Update Record' : 'Save Record' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
