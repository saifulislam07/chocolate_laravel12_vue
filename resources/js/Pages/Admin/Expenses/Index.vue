<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    expenses: Array,
    categories: Array
});

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

const totalExpenses = computed(() => {
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
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-money-bill-wave mr-2 text-danger"></i>Expenses</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-danger shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Expense</span>
                                <span class="info-box-number">৳{{ totalExpenses }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-light shadow-sm border">
                            <span class="info-box-icon text-danger"><i class="fas fa-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Expense Records</span>
                                <span class="info-box-number text-dark">{{ expenses.length }} Items</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-danger card-outline shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">Recent Expenses</h3>
                        <div class="card-tools">
                            <Link :href="route('admin.expense-categories.index')" class="btn btn-outline-danger btn-sm rounded-pill px-3 mr-2">
                                <i class="fas fa-tags mr-1"></i> Categories
                            </Link>
                            <button class="btn btn-danger btn-sm rounded-pill px-3" @click="openCreateModal">
                                <i class="fas fa-plus mr-1"></i> Record Expense
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped table-valign-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Reference</th>
                                    <th class="text-right">Amount</th>
                                    <th>Description</th>
                                    <th style="width: 120px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(expense, index) in expenses" :key="expense.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ expense.expense_date }}</td>
                                    <td><span class="badge badge-secondary">{{ expense.category?.name }}</span></td>
                                    <td><code>{{ expense.reference_no || 'N/A' }}</code></td>
                                    <td class="text-right font-weight-bold text-danger">৳{{ expense.amount }}</td>
                                    <td><small class="text-muted text-truncate d-inline-block" style="max-width: 200px">{{ expense.description }}</small></td>
                                    <td>
                                        <button class="btn btn-outline-primary btn-xs mr-1 p-1 px-2" @click="openEditModal(expense)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-xs p-1 px-2" @click="deleteExpense(expense.id)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="expenses.length === 0">
                                    <td colspan="7" class="text-center p-5 text-muted">No expense records found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
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
