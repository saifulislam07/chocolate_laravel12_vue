<script setup>
import BundleForm from './BundleForm.vue';

defineProps({
    bundle: Object,
    products: Array,
});
</script>

<template>
    <BundleForm
        title="Edit Bundle"
        :bundle="bundle"
        :products="products"
        :submit-route="route('admin.bundles.update', bundle.id)"
        method="put"
    />
</template>
