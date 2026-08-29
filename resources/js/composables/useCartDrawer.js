import { ref } from "vue";

const isCartDrawerOpen = ref(false);

export function openCartDrawer() {
    isCartDrawerOpen.value = true;
}

export function closeCartDrawer() {
    isCartDrawerOpen.value = false;
}

export function useCartDrawer() {
    return { isCartDrawerOpen, openCartDrawer, closeCartDrawer };
}
