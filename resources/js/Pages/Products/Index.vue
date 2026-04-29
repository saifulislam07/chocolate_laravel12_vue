<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import MainLayout from "@/Layouts/MainLayout.vue";
import {
    ChevronRightIcon,
    FunnelIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
} from "@heroicons/vue/20/solid";
import { HeartIcon } from "@heroicons/vue/24/outline";

function debounce(fn, delay) {
    let timeoutId;

    return (...args) => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

const props = defineProps({
    products: Object,
    categories: Array,
    selectedCategory: Object,
    filters: Object,
    priceBounds: Object,
});

const filterForm = ref({
    q: props.filters.q || "",
    category: props.filters.category || "",
    min_price: props.filters.min_price || "",
    max_price: props.filters.max_price || "",
    sort: props.filters.sort || "latest",
    sale: Boolean(props.filters.sale),
    in_stock: Boolean(props.filters.in_stock),
});

const displayedProducts = ref([...props.products.data]);
const showMobileFilters = ref(false);
const isLoadingMore = ref(false);
const shouldAppendNextPage = ref(false);
const fallbackImage = "/images/godiva/product_default.png";

const moneyFormatter = new Intl.NumberFormat("en-BD", {
    style: "currency",
    currency: "BDT",
    minimumFractionDigits: 0,
});

const activeFilterCount = computed(() => {
    return [
        filterForm.value.q,
        filterForm.value.category,
        filterForm.value.min_price,
        filterForm.value.max_price,
        filterForm.value.sale,
        filterForm.value.in_stock,
    ].filter(Boolean).length;
});

const hasMoreProducts = computed(() => Boolean(props.products.next_page_url));

watch(
    () => props.products.data,
    (products) => {
        if (shouldAppendNextPage.value) {
            const knownIds = new Set(displayedProducts.value.map((product) => product.id));
            displayedProducts.value = [
                ...displayedProducts.value,
                ...products.filter((product) => !knownIds.has(product.id)),
            ];
            shouldAppendNextPage.value = false;
            return;
        }

        displayedProducts.value = [...products];
    }
);

watch(
    () => props.filters,
    (filters) => {
        filterForm.value = {
            q: filters.q || "",
            category: filters.category || "",
            min_price: filters.min_price || "",
            max_price: filters.max_price || "",
            sort: filters.sort || "latest",
            sale: Boolean(filters.sale),
            in_stock: Boolean(filters.in_stock),
        };
    }
);

function formatMoney(value) {
    return moneyFormatter.format(Number(value || 0));
}

function applyFilters() {
    router.get(route("products.index"), cleanFilters(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function cleanFilters() {
    return Object.fromEntries(
        Object.entries(filterForm.value).filter(([, value]) => value !== "" && value !== false && value !== null)
    );
}

function resetFilters() {
    filterForm.value = {
        q: "",
        category: "",
        min_price: "",
        max_price: "",
        sort: "latest",
        sale: false,
        in_stock: false,
    };
    applyFilters();
}

function setCategory(slug) {
    filterForm.value.category = filterForm.value.category === slug ? "" : slug;
    applyFilters();
}

function setPriceRange(min, max) {
    filterForm.value.min_price = min;
    filterForm.value.max_price = max;
    applyFilters();
}

function loadMore() {
    if (!props.products.next_page_url || isLoadingMore.value) return;

    shouldAppendNextPage.value = true;
    router.get(props.products.next_page_url, {}, {
        preserveState: true,
        preserveScroll: true,
        only: ["products"],
        onStart: () => {
            isLoadingMore.value = true;
        },
        onFinish: () => {
            isLoadingMore.value = false;
        },
    });
}

function addToCart(productId) {
    router.post(route("cart.store"), { product_id: productId, quantity: 1 }, { preserveScroll: true });
}

function toggleWishlist(productId) {
    router.post(route("wishlist.toggle", productId), {}, { preserveScroll: true });
}

const debouncedApply = debounce(() => applyFilters(), 450);

watch(
    () => [filterForm.value.q, filterForm.value.min_price, filterForm.value.max_price],
    () => debouncedApply()
);

watch(
    () => [filterForm.value.sort, filterForm.value.sale, filterForm.value.in_stock],
    () => applyFilters()
);
</script>

<template>
    <MainLayout>
        <Head title="Shop Premium Chocolate | SweetChocholate" />

        <div class="bg-white">
            <div class="border-b border-[#eee4d8] bg-[#fcf8f3] py-14">
                <div class="mx-auto max-w-7xl px-6">
                    <nav class="mb-6 flex items-center justify-center gap-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        <Link href="/" class="transition hover:text-godiva-gold">Home</Link>
                        <ChevronRightIcon class="h-3 w-3" />
                        <span class="text-godiva-gold">Shop All</span>
                    </nav>
                    <div class="text-center">
                        <h1 class="font-serif text-5xl italic tracking-tight text-godiva-charcoal">
                            {{ selectedCategory?.name || 'The Collection' }}
                        </h1>
                        <p class="mt-4 text-[11px] font-bold uppercase tracking-[0.3em] text-godiva-gold">
                            {{ selectedCategory?.description || 'Find the chocolate that fits the moment' }}
                        </p>
                    </div>
                </div>
            </div>

            <main class="mx-auto max-w-7xl px-6 py-10">
                <section class="mb-10 grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div class="relative">
                        <MagnifyingGlassIcon class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="filterForm.q"
                            type="search"
                            placeholder="Search chocolate, SKU, flavor..."
                            class="h-12 w-full border border-gray-100 bg-white pl-11 pr-4 text-sm shadow-sm transition focus:border-godiva-gold focus:ring-0"
                        />
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            class="flex h-12 items-center gap-2 border border-gray-100 px-4 text-[10px] font-bold uppercase tracking-widest transition hover:border-godiva-gold lg:hidden"
                            @click="showMobileFilters = true"
                        >
                            <FunnelIcon class="h-4 w-4" />
                            Filters
                            <span v-if="activeFilterCount" class="rounded-full bg-godiva-charcoal px-2 py-0.5 text-white">{{ activeFilterCount }}</span>
                        </button>

                        <select
                            v-model="filterForm.sort"
                            class="h-12 border border-gray-100 bg-white px-4 text-[10px] font-bold uppercase tracking-widest shadow-sm focus:border-godiva-gold focus:ring-0"
                        >
                            <option value="latest">New Arrivals</option>
                            <option value="price_asc">Price: Low to High</option>
                            <option value="price_desc">Price: High to Low</option>
                        </select>
                    </div>
                </section>

                <div class="flex flex-col gap-10 lg:flex-row">
                    <aside class="hidden w-72 flex-shrink-0 lg:block">
                        <div class="sticky top-24 space-y-10">
                            <section>
                                <div class="mb-5 flex items-center justify-between">
                                    <h3 class="text-[11px] font-bold uppercase tracking-[0.2em] text-godiva-charcoal">Collections</h3>
                                    <button v-if="activeFilterCount" type="button" class="text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-red-500" @click="resetFilters">
                                        Clear
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <button
                                        v-for="cat in categories"
                                        :key="cat.id"
                                        type="button"
                                        class="flex w-full items-center justify-between border px-4 py-3 text-left text-xs transition"
                                        :class="filterForm.category === cat.slug ? 'border-godiva-gold bg-[#fcf8f3] text-godiva-charcoal' : 'border-gray-100 text-gray-500 hover:border-gray-200 hover:text-godiva-charcoal'"
                                        @click="setCategory(cat.slug)"
                                    >
                                        <span>{{ cat.name }}</span>
                                        <span class="text-[10px] text-gray-400">{{ cat.products_count }}</span>
                                    </button>
                                </div>
                            </section>

                            <section>
                                <h3 class="mb-5 text-[11px] font-bold uppercase tracking-[0.2em] text-godiva-charcoal">Price</h3>
                                <div class="grid grid-cols-2 gap-3">
                                    <input v-model="filterForm.min_price" type="number" placeholder="Min" class="h-11 border-gray-100 text-xs focus:border-godiva-gold focus:ring-0" />
                                    <input v-model="filterForm.max_price" type="number" placeholder="Max" class="h-11 border-gray-100 text-xs focus:border-godiva-gold focus:ring-0" />
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <button type="button" class="border border-gray-100 px-3 py-2 text-[10px] font-bold uppercase tracking-widest hover:border-godiva-gold" @click="setPriceRange('', 50)">Under 50</button>
                                    <button type="button" class="border border-gray-100 px-3 py-2 text-[10px] font-bold uppercase tracking-widest hover:border-godiva-gold" @click="setPriceRange(50, 100)">50 - 100</button>
                                    <button type="button" class="border border-gray-100 px-3 py-2 text-[10px] font-bold uppercase tracking-widest hover:border-godiva-gold" @click="setPriceRange(100, '')">100+</button>
                                </div>
                                <p class="mt-3 text-[10px] uppercase tracking-widest text-gray-400">
                                    Range {{ formatMoney(priceBounds.min) }} - {{ formatMoney(priceBounds.max) }}
                                </p>
                            </section>

                            <section class="space-y-3">
                                <label class="flex cursor-pointer items-center justify-between border border-gray-100 px-4 py-3 text-xs">
                                    <span>Sale items</span>
                                    <input v-model="filterForm.sale" type="checkbox" class="rounded border-gray-300 text-godiva-gold focus:ring-godiva-gold" />
                                </label>
                                <label class="flex cursor-pointer items-center justify-between border border-gray-100 px-4 py-3 text-xs">
                                    <span>In stock only</span>
                                    <input v-model="filterForm.in_stock" type="checkbox" class="rounded border-gray-300 text-godiva-gold focus:ring-godiva-gold" />
                                </label>
                            </section>
                        </div>
                    </aside>

                    <section class="min-w-0 flex-1">
                        <div class="mb-8 flex flex-col gap-3 border-b border-gray-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                Showing {{ displayedProducts.length }} of {{ products.total }} results
                            </p>
                            <div v-if="activeFilterCount" class="flex flex-wrap gap-2">
                                <span class="bg-[#fcf8f3] px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-godiva-charcoal">
                                    {{ activeFilterCount }} active
                                </span>
                                <button type="button" class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-red-500" @click="resetFilters">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <div v-if="displayedProducts.length" class="grid grid-cols-1 gap-x-8 gap-y-12 sm:grid-cols-2 xl:grid-cols-3">
                            <article v-for="product in displayedProducts" :key="product.id" class="group relative flex flex-col bg-white">
                                <div class="relative aspect-square overflow-hidden bg-white p-4">
                                    <span v-if="product.is_new" class="absolute left-4 top-4 z-10 bg-godiva-pink px-3 py-1 text-[9px] font-bold uppercase tracking-widest text-godiva-charcoal">New</span>
                                    <button
                                        type="button"
                                        class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white text-godiva-charcoal shadow-sm transition hover:text-red-500"
                                        :class="{ 'text-red-500': product.is_wishlisted }"
                                        @click="toggleWishlist(product.id)"
                                    >
                                        <HeartIcon class="h-4 w-4" :class="{ 'fill-current': product.is_wishlisted }" />
                                    </button>
                                    <img
                                        :src="product.images?.[0]?.image_path || fallbackImage"
                                        :alt="product.name"
                                        class="h-full w-full object-contain transition duration-500 group-hover:scale-105"
                                    />
                                    <div class="absolute inset-x-0 bottom-4 flex translate-y-4 justify-center px-4 opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100">
                                        <button type="button" class="w-full bg-godiva-charcoal py-3 text-[10px] font-bold uppercase tracking-widest text-white transition hover:bg-godiva-gold" @click="addToCart(product.id)">
                                            Add to Bag
                                        </button>
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col p-6 text-center">
                                    <h3 class="font-serif text-lg uppercase leading-tight tracking-tight">
                                        <Link :href="route('products.show', product.slug)" class="transition hover:text-godiva-gold">{{ product.name }}</Link>
                                    </h3>
                                    <div class="mt-4 flex flex-col items-center justify-center gap-1">
                                        <span v-if="product.compare_at_price > product.price" class="text-xs tracking-widest text-gray-400 line-through">{{ formatMoney(product.compare_at_price) }}</span>
                                        <span class="font-serif text-xl" :class="product.compare_at_price > product.price ? 'text-red-600' : 'text-godiva-charcoal'">{{ formatMoney(product.price) }}</span>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div v-else class="rounded-sm border border-dashed border-gray-200 py-24 text-center">
                            <p class="font-serif text-2xl italic text-gray-400">No treasures found matching your search</p>
                            <button type="button" class="mt-6 text-[10px] font-bold uppercase tracking-[0.2em] text-godiva-gold hover:underline" @click="resetFilters">Clear all filters</button>
                        </div>

                        <div v-if="hasMoreProducts" class="mt-16 flex justify-center border-t border-gray-100 pt-10">
                            <button
                                type="button"
                                class="min-w-48 border border-godiva-charcoal px-10 py-4 text-[11px] font-bold uppercase tracking-[0.2em] text-godiva-charcoal transition hover:bg-godiva-charcoal hover:text-white disabled:cursor-wait disabled:opacity-50"
                                :disabled="isLoadingMore"
                                @click="loadMore"
                            >
                                {{ isLoadingMore ? "Loading..." : "Load More" }}
                            </button>
                        </div>
                    </section>
                </div>
            </main>
        </div>

        <div v-if="showMobileFilters" class="fixed inset-0 z-[100] overflow-y-auto bg-white lg:hidden">
            <div class="sticky top-0 flex items-center justify-between border-b border-gray-100 bg-white px-6 py-4">
                <span class="text-xs font-bold uppercase tracking-widest">Filter & Sort</span>
                <button class="p-2" @click="showMobileFilters = false"><XMarkIcon class="h-6 w-6" /></button>
            </div>

            <div class="space-y-8 p-6">
                <section>
                    <h3 class="mb-4 text-[10px] font-bold uppercase tracking-[0.25em] text-gray-400">Collections</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            v-for="cat in categories"
                            :key="cat.id"
                            type="button"
                            class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest transition"
                            :class="filterForm.category === cat.slug ? 'bg-godiva-gold text-white' : 'border border-gray-100 bg-white'"
                            @click="setCategory(cat.slug)"
                        >
                            {{ cat.name }}
                        </button>
                    </div>
                </section>

                <section>
                    <h3 class="mb-4 text-[10px] font-bold uppercase tracking-[0.25em] text-gray-400">Price Range</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <input v-model="filterForm.min_price" type="number" placeholder="Min" class="w-full border-gray-100 p-4 text-xs" />
                        <input v-model="filterForm.max_price" type="number" placeholder="Max" class="w-full border-gray-100 p-4 text-xs" />
                    </div>
                </section>

                <section class="space-y-3">
                    <label class="flex items-center justify-between border border-gray-100 px-4 py-4 text-xs">
                        <span>Sale items</span>
                        <input v-model="filterForm.sale" type="checkbox" class="rounded border-gray-300 text-godiva-gold focus:ring-godiva-gold" />
                    </label>
                    <label class="flex items-center justify-between border border-gray-100 px-4 py-4 text-xs">
                        <span>In stock only</span>
                        <input v-model="filterForm.in_stock" type="checkbox" class="rounded border-gray-300 text-godiva-gold focus:ring-godiva-gold" />
                    </label>
                </section>

                <button class="w-full bg-godiva-charcoal py-5 text-[11px] font-bold uppercase tracking-[0.3em] text-white" @click="applyFilters(); showMobileFilters = false">
                    Apply Filters
                </button>
                <button class="w-full border border-gray-100 py-5 text-[11px] font-bold uppercase tracking-[0.3em] text-gray-400" @click="resetFilters(); showMobileFilters = false">
                    Clear All
                </button>
            </div>
        </div>
    </MainLayout>
</template>
