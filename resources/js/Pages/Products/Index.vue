<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, onMounted } from "vue";
import MainLayout from "@/Layouts/MainLayout.vue";
import { 
    ChevronRightIcon, 
    FunnelIcon, 
    ChevronDownIcon,
    XMarkIcon
} from "@heroicons/vue/20/solid";

// Custom debounce function to avoid lodash dependency issues
function debounce(fn, delay) {
    let timeoutId;
    return (...args) => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fn(...args);
        }, delay);
    };
}

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const filterForm = ref({
    category: props.filters.category || "",
    min_price: props.filters.min_price || "",
    max_price: props.filters.max_price || "",
    sort: props.filters.sort || "latest",
});

const showMobileFilters = ref(false);

const moneyFormatter = new Intl.NumberFormat("en-BD", { 
    style: "currency", 
    currency: "BDT",
    minimumFractionDigits: 0,
});

function formatMoney(value) {
    return moneyFormatter.format(Number(value || 0));
}

function applyFilters() {
    router.get(route('products.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function resetFilters() {
    filterForm.value = {
        category: "",
        min_price: "",
        max_price: "",
        sort: "latest",
    };
    applyFilters();
}

function addToCart(productId) {
    router.post(route("cart.store"), { product_id: productId, quantity: 1 }, { preserveScroll: true });
}

watch(
    () => filterForm.value.sort,
    () => applyFilters()
);

const debouncedApply = debounce(() => applyFilters(), 500);

watch(
    () => [filterForm.value.min_price, filterForm.value.max_price],
    () => debouncedApply()
);

const fallbackImage = "/images/godiva/product_default.png";
</script>

<template>
    <MainLayout>
        <Head title="Shop Premium Chocolate | SweetChocholate" />

        <div class="bg-white">
            <!-- Hero Header -->
            <div class="bg-[#fcf8f3] py-16 border-b border-[#eee4d8]">
                <div class="mx-auto max-w-7xl px-6 text-center">
                    <nav class="flex items-center justify-center gap-2 text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-6">
                        <Link href="/" class="hover:text-godiva-gold transition">Home</Link>
                        <ChevronRightIcon class="h-3 w-3" />
                        <span class="text-godiva-gold">Shop All</span>
                    </nav>
                    <h1 class="font-serif text-5xl italic tracking-tight text-godiva-charcoal">The Collection</h1>
                    <p class="mt-4 text-[11px] font-bold uppercase tracking-[0.3em] text-godiva-gold">Experience Artisan Belgian Perfection</p>
                </div>
            </div>

            <main class="mx-auto max-w-7xl px-6 py-12">
                <div class="flex flex-col lg:flex-row gap-12">
                    
                    <!-- Sidebar Filters (Desktop) -->
                    <aside class="hidden lg:block w-64 flex-shrink-0">
                        <div class="sticky top-24 space-y-12">
                            <!-- Categories -->
                            <section>
                                <h3 class="text-[11px] font-bold uppercase tracking-[0.2em] text-godiva-charcoal mb-6 flex items-center justify-between">
                                    Collections
                                    <span v-if="filterForm.category" @click="filterForm.category = ''; applyFilters()" class="text-[9px] text-gray-400 cursor-pointer hover:text-red-500 font-normal normal-case tracking-normal">Clear</span>
                                </h3>
                                <div class="space-y-4">
                                    <label v-for="cat in categories" :key="cat.id" class="flex items-center group cursor-pointer">
                                        <input 
                                            type="radio" 
                                            v-model="filterForm.category" 
                                            :value="cat.slug" 
                                            @change="applyFilters"
                                            class="sr-only"
                                        />
                                        <span 
                                            class="text-xs transition-colors duration-300"
                                            :class="filterForm.category === cat.slug ? 'text-godiva-gold font-bold' : 'text-gray-500 group-hover:text-godiva-charcoal'"
                                        >
                                            {{ cat.name }}
                                        </span>
                                    </label>
                                </div>
                            </section>

                            <!-- Price Range -->
                            <section>
                                <h3 class="text-[11px] font-bold uppercase tracking-[0.2em] text-godiva-charcoal mb-6">Price Range</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">৳</span>
                                            <input 
                                                type="number" 
                                                v-model="filterForm.min_price" 
                                                placeholder="Min"
                                                class="w-full pl-6 pr-3 py-2 text-xs border border-gray-100 rounded-sm focus:border-godiva-gold focus:ring-0 transition"
                                            />
                                        </div>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">৳</span>
                                            <input 
                                                type="number" 
                                                v-model="filterForm.max_price" 
                                                placeholder="Max"
                                                class="w-full pl-6 pr-3 py-2 text-xs border border-gray-100 rounded-sm focus:border-godiva-gold focus:ring-0 transition"
                                            />
                                        </div>
                                    </div>
                                    <div v-if="filterForm.min_price || filterForm.max_price" class="text-right">
                                        <button @click="filterForm.min_price = ''; filterForm.max_price = ''; applyFilters()" class="text-[9px] text-gray-400 hover:text-red-500 uppercase tracking-widest font-bold">Reset Price</button>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </aside>

                    <!-- Main Content -->
                    <div class="flex-1">
                        <!-- Toolbar -->
                        <div class="flex items-center justify-between mb-10 border-b border-gray-50 pb-6">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                Showing {{ products.from || 0 }} - {{ products.to || 0 }} of {{ products.total }} results
                            </p>
                            
                            <div class="flex items-center gap-6">
                                <!-- Mobile Filter Trigger -->
                                <button @click="showMobileFilters = true" class="lg:hidden flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest">
                                    <FunnelIcon class="h-4 w-4" />
                                    Filters
                                </button>

                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mr-2">Sort By</span>
                                    <select 
                                        v-model="filterForm.sort" 
                                        class="bg-transparent border-none text-[10px] font-bold uppercase tracking-widest focus:ring-0 cursor-pointer p-0 pr-8"
                                    >
                                        <option value="latest">New Arrivals</option>
                                        <option value="price_asc">Price: Low to High</option>
                                        <option value="price_desc">Price: High to Low</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Product Grid -->
                        <div v-if="products.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12">
                            <article v-for="product in products.data" :key="product.id" class="group relative flex flex-col bg-white">
                                <div class="relative aspect-square overflow-hidden bg-white p-4">
                                    <span v-if="product.is_new" class="absolute top-4 left-4 z-10 bg-godiva-pink px-3 py-1 text-[9px] font-bold uppercase tracking-widest text-godiva-charcoal">New</span>
                                    <img 
                                        :src="product.images?.[0]?.image_path || fallbackImage" 
                                        :alt="product.name" 
                                        class="h-full w-full object-contain transition duration-500 group-hover:scale-105" 
                                    />
                                    <div class="absolute inset-x-0 bottom-4 flex translate-y-4 justify-center opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100 px-4">
                                        <button 
                                            type="button" 
                                            class="w-full bg-godiva-charcoal py-3 text-[10px] font-bold uppercase tracking-widest text-white transition hover:bg-godiva-gold" 
                                            @click="addToCart(product.id)"
                                        >
                                            Add to Bag
                                        </button>
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col p-6 text-center">
                                    <h3 class="font-serif text-lg tracking-tight uppercase leading-tight">
                                        <Link :href="route('products.show', product.slug)" class="hover:text-godiva-gold transition">{{ product.name }}</Link>
                                    </h3>
                                    <div class="mt-4 flex flex-col items-center justify-center gap-1">
                                        <span v-if="product.compare_at_price > product.price" class="text-xs text-gray-400 line-through tracking-widest">{{ formatMoney(product.compare_at_price) }}</span>
                                        <span class="font-serif text-xl" :class="product.compare_at_price > product.price ? 'text-red-600' : 'text-godiva-charcoal'">{{ formatMoney(product.price) }}</span>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-24 text-center border border-dashed border-gray-200 rounded-sm">
                            <p class="font-serif text-2xl italic text-gray-400">No treasures found matching your search</p>
                            <button @click="resetFilters" class="mt-6 text-[10px] font-bold uppercase tracking-[0.2em] text-godiva-gold hover:underline">Clear all filters</button>
                        </div>

                        <!-- Pagination -->
                        <div v-if="products.total > products.per_page" class="mt-20 flex justify-center border-t border-gray-100 pt-10">
                            <nav class="flex gap-2">
                                <Link 
                                    v-for="link in products.links" 
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    class="h-10 px-4 flex items-center justify-center text-[10px] font-bold uppercase tracking-widest transition-all rounded-sm"
                                    :class="[
                                        link.active ? 'bg-godiva-gold text-white shadow-sm' : 'text-gray-400 border border-transparent hover:border-gray-200',
                                        !link.url ? 'opacity-30 cursor-not-allowed hidden' : ''
                                    ]"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Mobile Filters Modal -->
        <div v-if="showMobileFilters" class="fixed inset-0 z-[100] bg-white lg:hidden overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white">
                <span class="text-xs font-bold uppercase tracking-widest">Filter & Sort</span>
                <button @click="showMobileFilters = false" class="p-2"><XMarkIcon class="h-6 w-6" /></button>
            </div>
            
            <div class="p-6 space-y-10">
                <section>
                    <h3 class="text-[10px] font-bold uppercase tracking-[0.25em] text-gray-400 mb-6">Collections</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            v-for="cat in categories" 
                            :key="cat.id"
                            @click="filterForm.category = cat.slug; applyFilters(); showMobileFilters = false"
                            class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest border transition"
                            :class="filterForm.category === cat.slug ? 'bg-godiva-gold text-white border-godiva-gold' : 'bg-white border-gray-100'"
                        >
                            {{ cat.name }}
                        </button>
                    </div>
                </section>

                <section>
                    <h3 class="text-[10px] font-bold uppercase tracking-[0.25em] text-gray-400 mb-6 font-serif">Price Range</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="number" v-model="filterForm.min_price" placeholder="Min ৳" class="w-full border-gray-100 p-4 text-xs" />
                        <input type="number" v-model="filterForm.max_price" placeholder="Max ৳" class="w-full border-gray-100 p-4 text-xs" />
                    </div>
                </section>

                <button @click="applyFilters(); showMobileFilters = false" class="w-full bg-godiva-charcoal text-white py-5 text-[11px] font-bold uppercase tracking-[0.3em]">Apply Filters</button>
                <button @click="resetFilters(); showMobileFilters = false" class="w-full border border-gray-100 py-5 text-[11px] font-bold uppercase tracking-[0.3em] text-gray-400">Clear All</button>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
</style>
