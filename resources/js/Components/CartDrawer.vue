<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, onMounted, watch } from "vue";
import { XMarkIcon, PlusIcon, MinusIcon, ArrowRightIcon, ShoppingBagIcon } from "@heroicons/vue/24/outline";
import { useCartDrawer } from "@/composables/useCartDrawer";

const page = usePage();
const { isCartDrawerOpen, closeCartDrawer } = useCartDrawer();

const fallbackImage = "/images/godiva/product_default.png";
const items = computed(() => page.props.cartItems || []);
const subtotal = computed(() => Number(page.props.cartSubtotal || 0));
const count = computed(() => page.props.cartCount || 0);

const moneyFormatter = new Intl.NumberFormat("en-BD", {
    style: "currency",
    currency: "BDT",
    minimumFractionDigits: 0,
});

function formatMoney(amount) {
    return moneyFormatter.format(Number(amount || 0));
}

function productHref(item) {
    return item.slug ? route("products.show", item.slug) : route("cart.index");
}

function updateQuantity(item, quantity) {
    if (quantity < 1 || quantity > 20 || quantity === item.quantity) {
        return;
    }

    router.patch(route("cart.update", item.id), { quantity }, { preserveScroll: true, preserveState: true });
}

function removeItem(itemId) {
    router.delete(route("cart.destroy", itemId), { preserveScroll: true, preserveState: true });
}

function onKeydown(event) {
    if (event.key === "Escape") {
        closeCartDrawer();
    }
}

watch(isCartDrawerOpen, (open) => {
    document.body.style.overflow = open ? "hidden" : "";
});

onMounted(() => {
    document.addEventListener("keydown", onKeydown);

    if (isCartDrawerOpen.value) {
        document.body.style.overflow = "hidden";
    }
});
onBeforeUnmount(() => {
    document.removeEventListener("keydown", onKeydown);
    document.body.style.overflow = "";
});
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition
            appear
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isCartDrawerOpen"
                class="fixed inset-0 z-[110] bg-cocov-brown-dark/40"
                @click="closeCartDrawer"
            ></div>
        </Transition>

        <!-- Panel -->
        <Transition
            appear
            enter-active-class="transition ease-out duration-300"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <aside
                v-if="isCartDrawerOpen"
                class="fixed inset-y-0 right-0 z-[120] flex w-full max-w-md flex-col bg-white text-cocov-text shadow-2xl"
                role="dialog"
                aria-modal="true"
                aria-label="Shopping bag"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-cocov-line px-6 py-5">
                    <h2 class="font-heading text-xl uppercase tracking-tight">
                        Your Bag
                        <span class="ml-1 text-sm text-cocov-text/40">({{ count }})</span>
                    </h2>
                    <button type="button" class="text-cocov-text/40 transition hover:text-cocov-gold" aria-label="Close bag" @click="closeCartDrawer">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>

                <!-- Empty state -->
                <div v-if="!items.length" class="flex flex-1 flex-col items-center justify-center px-8 text-center">
                    <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-cocov-offer/20">
                        <ShoppingBagIcon class="h-9 w-9 text-cocov-gold" />
                    </div>
                    <p class="font-heading text-xl uppercase">Your bag is empty.</p>
                    <button
                        type="button"
                        class="mt-8 rounded-[3px] bg-cocov-gold px-10 py-3.5 text-[11px] font-bold uppercase tracking-widest text-white transition hover:bg-[#e0851a]"
                        @click="closeCartDrawer"
                    >
                        Continue Shopping
                    </button>
                </div>

                <template v-else>
                    <!-- Items -->
                    <div class="flex-1 overflow-y-auto px-6 py-6">
                        <article
                            v-for="item in items"
                            :key="item.id"
                            class="flex gap-4 border-b border-gray-50 py-5 first:pt-0 last:border-0"
                        >
                            <Link
                                :href="productHref(item)"
                                class="h-24 w-24 flex-shrink-0 border border-gray-100 bg-white p-2"
                                @click="closeCartDrawer"
                            >
                                <img :src="item.image || fallbackImage" :alt="item.name" class="h-full w-full object-contain" />
                            </Link>

                            <div class="flex min-w-0 flex-1 flex-col justify-between">
                                <div class="flex items-start justify-between gap-3">
                                    <Link
                                        :href="productHref(item)"
                                        class="min-w-0 font-heading text-sm uppercase leading-snug transition hover:text-cocov-gold"
                                        @click="closeCartDrawer"
                                    >{{ item.name }}</Link>
                                    <button type="button" class="shrink-0 text-gray-400 transition hover:text-cocov-gold" aria-label="Remove item" @click="removeItem(item.id)">
                                        <XMarkIcon class="h-4 w-4" />
                                    </button>
                                </div>

                                <div class="mt-3 flex items-end justify-between gap-3">
                                    <div class="flex h-9 w-24 items-center justify-between border border-gray-200 px-3">
                                        <button type="button" class="text-gray-400 transition hover:text-cocov-gold" aria-label="Decrease quantity" @click="updateQuantity(item, item.quantity - 1)">
                                            <MinusIcon class="h-3 w-3" />
                                        </button>
                                        <span class="text-xs font-bold">{{ item.quantity }}</span>
                                        <button type="button" class="text-gray-400 transition hover:text-cocov-gold" aria-label="Increase quantity" @click="updateQuantity(item, item.quantity + 1)">
                                            <PlusIcon class="h-3 w-3" />
                                        </button>
                                    </div>
                                    <p class="font-heading text-base">{{ formatMoney(item.line_total) }}</p>
                                </div>
                            </div>
                        </article>
                    </div>

                    <!-- Footer -->
                    <div class="border-t border-cocov-line px-6 py-6">
                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-widest">
                            <span>Subtotal</span>
                            <span class="font-heading text-xl tracking-normal">{{ formatMoney(subtotal) }}</span>
                        </div>
                        <p class="mt-2 text-[9px] uppercase tracking-widest text-gray-400">Shipping calculated at checkout</p>

                        <Link
                            :href="route('checkout.index')"
                            class="mt-6 flex w-full items-center justify-center gap-3 rounded-[3px] bg-cocov-gold py-4 text-[11px] font-bold uppercase tracking-widest text-white transition hover:bg-[#e0851a]"
                            @click="closeCartDrawer"
                        >
                            Checkout
                            <ArrowRightIcon class="h-4 w-4" />
                        </Link>
                        <Link
                            :href="route('cart.index')"
                            class="mt-3 flex w-full items-center justify-center rounded-[3px] border border-cocov-line py-3.5 text-[11px] font-bold uppercase tracking-widest text-cocov-text transition hover:border-cocov-gold hover:text-cocov-gold"
                            @click="closeCartDrawer"
                        >
                            View Bag
                        </Link>
                    </div>
                </template>
            </aside>
        </Transition>
    </Teleport>
</template>
