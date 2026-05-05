<script setup>
import BundleForm from './BundleForm.vue';

defineProps({
    products: Array,
});
</script>

<template>
    <BundleForm
        title="Create Bundle"
        :products="products"
        :submit-route="route('admin.bundles.store')"
    />
</template>
