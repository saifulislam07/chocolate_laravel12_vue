<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    headers: {
        type: Array,
        required: true
    },
    searchPlaceholder: {
        type: String,
        default: 'Search records...'
    },
    selectable: {
        type: Boolean,
        default: false
    }
});

const searchQuery = ref('');
const sortKey = ref('');
const sortOrder = ref(1); // 1 for asc, -1 for desc

const handleSort = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value * -1;
    } else {
        sortKey.value = key;
        sortOrder.value = 1;
    }
};

const filteredAndSortedItems = computed(() => {
    let result = [...props.items];

    // Search logic
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(item => {
            return Object.values(item).some(val => {
                if (val === null || val === undefined) return false;
                return String(val).toLowerCase().includes(query);
            });
        });
    }

    // Sort logic
    if (sortKey.value) {
        result.sort((a, b) => {
            const aVal = a[sortKey.value];
            const bVal = b[sortKey.value];
            
            if (aVal < bVal) return -1 * sortOrder.value;
            if (aVal > bVal) return 1 * sortOrder.value;
            return 0;
        });
    }

    return result;
});

const getHeaderLabel = (header) => typeof header === 'object' ? header.label : header;
const getHeaderKey = (header) => typeof header === 'object' ? header.key : header;
const isSortable = (header) => typeof header === 'object' ? header.sortable !== false : true;

</script>

<template>
    <div class="premium-table-container">
        <!-- Table Header Actions -->
        <div class="table-actions-bar mb-4 d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center flex-wrap" style="gap: 15px;">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        :placeholder="searchPlaceholder" 
                        class="premium-search-input"
                    >
                </div>
                <slot name="filters"></slot>
            </div>
            <div class="extra-actions">
                <slot name="extra-actions"></slot>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="table-responsive premium-card shadow-sm border-0 rounded-lg">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th v-if="selectable" style="width: 40px" class="pl-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="checkAll">
                                <label class="custom-control-label" for="checkAll"></label>
                            </div>
                        </th>
                        <th 
                            v-for="header in headers" 
                            :key="getHeaderKey(header)"
                            @click="isSortable(header) ? handleSort(getHeaderKey(header)) : null"
                            :class="[
                                isSortable(header) ? 'sortable-header' : '',
                                typeof header === 'object' && header.class ? header.class : ''
                            ]"
                            :style="typeof header === 'object' && header.width ? { width: header.width } : {}"
                        >
                            <div class="d-flex align-items-center justify-content-between">
                                <span>{{ getHeaderLabel(header) }}</span>
                                <span v-if="isSortable(header)" class="sort-icons ml-2">
                                    <i v-if="sortKey !== getHeaderKey(header)" class="fas fa-sort text-muted-extra text-xs"></i>
                                    <i v-else :class="sortOrder === 1 ? 'fas fa-sort-up text-primary' : 'fas fa-sort-down text-primary'"></i>
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in filteredAndSortedItems" :key="item.id || index">
                        <td v-if="selectable" class="pl-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" :id="'check-' + (item.id || index)">
                                <label class="custom-control-label" :for="'check-' + (item.id || index)"></label>
                            </div>
                        </td>
                        <template v-for="header in headers" :key="getHeaderKey(header)">
                            <td :class="typeof header === 'object' && header.cellClass ? header.cellClass : ''">
                                <slot :name="'cell-' + getHeaderKey(header)" :item="item" :index="index">
                                    {{ item[getHeaderKey(header)] }}
                                </slot>
                            </td>
                        </template>
                    </tr>
                    <!-- Empty State -->
                    <tr v-if="filteredAndSortedItems.length === 0">
                        <td :colspan="headers.length + (selectable ? 1 : 0)" class="empty-state-cell p-5 text-center">
                            <div class="py-5">
                                <i class="fas fa-folder-open text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                <h5 class="text-muted font-weight-normal">No records found</h5>
                                <p class="text-xs text-muted">Try adjusting your search criteria</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <!-- Footer Slot -->
                <tfoot v-if="$slots.footer">
                    <slot name="footer" :filteredItems="filteredAndSortedItems"></slot>
                </tfoot>
            </table>
        </div>
        
        <!-- Pagination Placeholder -->
        <div class="table-footer mt-3 d-flex justify-content-between align-items-center px-2">
            <span class="text-xs text-muted">Showing {{ filteredAndSortedItems.length }} of {{ items.length }} total records</span>
            <slot name="pagination"></slot>
        </div>
    </div>
</template>

<style scoped>
.premium-table-container {
    width: 100%;
}

.premium-card {
    background: #ffffff;
    overflow: hidden;
}

.table {
    border-collapse: separate;
    border-spacing: 0;
}

/* Header Styling */
thead th {
    background-color: #f8fafc;
    color: #64748b;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 1rem 1.25rem;
    border-bottom: 2px solid #f1f5f9 !important;
    border-top: none !important;
    white-space: nowrap;
}

.sortable-header {
    cursor: pointer;
    transition: all 0.2s ease;
}

.sortable-header:hover {
    background-color: #f1f5f9;
    color: #334155;
}

/* Body Cell Styling - THIS ADDRESSES "TEXT TOO BIG" */
tbody td {
    padding: 1rem 1.25rem;
    vertical-align: middle;
    font-size: 0.85rem; /* Reduced font size as requested */
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
    transition: background-color 0.2s ease;
}

tbody tr:hover td {
    background-color: #fcfdfe;
}

/* Search Input Styling */
.search-wrapper {
    position: relative;
    width: 300px;
}

.search-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.9rem;
}

.premium-search-input {
    width: 100%;
    padding: 0.6rem 1rem 0.6rem 2.5rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
    outline: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.premium-search-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.text-muted-extra {
    color: #cbd5e1;
}

.text-xs { font-size: 0.7rem; }

/* Mobile optimization */
@media (max-width: 768px) {
    .search-wrapper {
        width: 100%;
        margin-bottom: 1rem;
    }
}
</style>
