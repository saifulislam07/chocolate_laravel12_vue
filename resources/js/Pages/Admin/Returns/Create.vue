<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    order: { type: Object, required: true },
});

const form = useForm({
    order_id: props.order.id,
    reason: '',
    refund_method: props.order.has_customer ? 'wallet' : 'cash',
    items: props.order.items
        .filter((item) => item.returnable > 0)
        .map((item) => ({
            order_item_id: item.id,
            product_name: item.product_name,
            price: item.price,
            returnable: item.returnable,
            selected: false,
            quantity: item.returnable,
            condition: 'resellable',
        })),
});

const selectedItems = computed(() => form.items.filter((item) => item.selected));

const estimatedRefund = computed(() =>
    selectedItems.value.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity || 0)), 0)
);

function formatMoney(value) {
    return '৳' + Number(value || 0).toFixed(2);
}

function submit() {
    form.transform((data) => ({
        ...data,
        items: data.items
            .filter((item) => item.selected)
            .map((item) => ({
                order_item_id: item.order_item_id,
                quantity: item.quantity,
                condition: item.condition,
            })),
    })).post(route('admin.returns.store'));
}
</script>

<template>
    <Head title="Process Return" />

    <AdminLayout>
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="m-0 text-dark font-bold h3">Process Return</h1>
                        <p class="text-muted text-sm mb-0">Order #{{ order.order_number }} ({{ order.order_source === 'pos' ? 'POS' : 'Online' }})</p>
                    </div>
                    <Link :href="route('admin.sales.show', order.id)" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Sale
                    </Link>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div v-if="!order.has_customer" class="alert alert-warning">
                            This order has no linked customer, so a wallet refund isn't available — choose an external refund method instead.
                        </div>

                        <div v-if="form.items.length === 0" class="text-center text-muted py-5">
                            Every item on this order has already been fully returned.
                        </div>

                        <div v-else class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light text-xs text-uppercase text-muted">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th style="width: 140px;">Return Qty</th>
                                    <th style="width: 160px;">Condition</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in form.items" :key="item.order_item_id">
                                    <td><input type="checkbox" v-model="item.selected"></td>
                                    <td>
                                        <span class="font-weight-bold">{{ item.product_name }}</span>
                                        <div class="text-xs text-muted">{{ item.returnable }} returnable</div>
                                    </td>
                                    <td>{{ formatMoney(item.price) }}</td>
                                    <td>
                                        <input
                                            type="number"
                                            v-model.number="item.quantity"
                                            :min="1"
                                            :max="item.returnable"
                                            :disabled="!item.selected"
                                            class="form-control form-control-sm"
                                        >
                                    </td>
                                    <td>
                                        <SearchableSelect
                                            v-model="item.condition"
                                            :options="[
                                                { value: 'resellable', label: 'Resellable' },
                                                { value: 'damaged', label: 'Damaged' },
                                            ]"
                                            :disabled="!item.selected"
                                            control-class="form-control form-control-sm"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6 form-group">
                                <label class="text-xs font-bold text-muted text-uppercase">Reason (optional)</label>
                                <RichTextEditor v-model="form.reason" :height="110" toolbar="basic" :invalid="!!form.errors.reason" placeholder="e.g. Customer changed mind, wrong item shipped..." />
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-xs font-bold text-muted text-uppercase">Refund Method</label>
                                <SearchableSelect
                                    v-model="form.refund_method"
                                    :options="[
                                        { value: 'wallet', label: 'Store Credit (Wallet)' },
                                        { value: 'cash', label: 'Cash (external)' },
                                        { value: 'bkash', label: 'bKash (external)' },
                                        { value: 'nagad', label: 'Nagad (external)' },
                                        { value: 'bank', label: 'Bank Transfer (external)' },
                                        { value: 'card', label: 'Card Reversal (external)' },
                                    ]"
                                    :option-disabled="(option) => option.value === 'wallet' && !order.has_customer"
                                />
                                <p class="text-xs text-muted mt-1 mb-0" v-if="form.refund_method === 'wallet'">
                                    Credits the customer's store wallet automatically.
                                </p>
                                <p class="text-xs text-muted mt-1 mb-0" v-else>
                                    Recorded for accounting only — process the actual {{ form.refund_method }} refund outside the system.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-4 mt-2">
                            <div>
                                <span class="text-xs text-uppercase text-muted d-block">Estimated Refund</span>
                                <span class="h4 font-weight-bold text-success">{{ formatMoney(estimatedRefund) }}</span>
                            </div>
                            <button
                                type="button"
                                class="btn btn-danger btn-lg"
                                :disabled="form.processing || selectedItems.length === 0"
                                @click="submit"
                            >
                                <i class="fas fa-undo mr-1"></i> Process Return
                            </button>
                        </div>
                        <p v-if="form.errors.items" class="text-danger text-sm mt-2 mb-0">{{ form.errors.items }}</p>
                        <p v-if="form.errors.refund_method" class="text-danger text-sm mt-2 mb-0">{{ form.errors.refund_method }}</p>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
