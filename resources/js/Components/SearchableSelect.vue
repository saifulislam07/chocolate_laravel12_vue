<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    modelValue: { type: [String, Number, Boolean, Object], default: '' },
    options: { type: Array, default: () => [] },
    labelKey: { type: String, default: 'label' },
    valueKey: { type: String, default: 'value' },
    // Optional second line under the label — SKU, price, phone number, etc.
    hintKey: { type: String, default: '' },
    placeholder: { type: String, default: 'Select...' },
    searchPlaceholder: { type: String, default: 'Search...' },
    emptyText: { type: String, default: 'No match found' },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    invalid: { type: Boolean, default: false },
    clearable: { type: Boolean, default: false },
    // Swapped wholesale so a Tailwind-styled page can pass its own classes
    // instead of Bootstrap's.
    controlClass: { type: [String, Array, Object], default: 'form-control' },
    optionDisabled: { type: Function, default: null },
    name: { type: String, default: '' },
    // A search box on a three-item list is just noise, so it only appears once
    // the list is long enough to be worth filtering.
    searchThreshold: { type: Number, default: 7 },
});

const emit = defineEmits(['update:modelValue', 'change']);

const root = ref(null);
const trigger = ref(null);
const menu = ref(null);
const searchInput = ref(null);
const open = ref(false);
const search = ref('');
const highlighted = ref(-1);
const menuStyle = ref({});

const normalized = computed(() => props.options.map((option) => {
    if (option === null || typeof option !== 'object') {
        return { value: option, label: String(option ?? ''), hint: '', disabled: false, raw: option };
    }

    return {
        value: option[props.valueKey],
        label: String(option[props.labelKey] ?? ''),
        hint: props.hintKey ? String(option[props.hintKey] ?? '') : '',
        disabled: props.optionDisabled ? !!props.optionDisabled(option) : !!option.disabled,
        raw: option,
    };
}));

// Inertia forms hand back ids as strings after a failed submit while the
// options still carry numbers, so values are matched loosely.
function sameValue(a, b) {
    const aEmpty = a === null || a === undefined || a === '';
    const bEmpty = b === null || b === undefined || b === '';
    if (aEmpty || bEmpty) return aEmpty && bEmpty;

    return String(a) === String(b);
}

const selected = computed(() => normalized.value.find((option) => sameValue(option.value, props.modelValue)) || null);
const hasValue = computed(() => !!selected.value);
const showSearch = computed(() => normalized.value.length >= props.searchThreshold);

const filtered = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return normalized.value;

    return normalized.value.filter((option) => `${option.label} ${option.hint}`.toLowerCase().includes(query));
});

function positionMenu() {
    const el = trigger.value;
    if (!el) return;

    const rect = el.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    // Flip above the field when the bottom of the viewport would cut the list off.
    const flipUp = spaceBelow < 240 && rect.top > spaceBelow;

    menuStyle.value = {
        left: `${rect.left}px`,
        width: `${rect.width}px`,
        maxHeight: `${Math.max(160, Math.min(320, flipUp ? rect.top - 12 : spaceBelow - 12))}px`,
        ...(flipUp ? { bottom: `${window.innerHeight - rect.top + 4}px` } : { top: `${rect.bottom + 4}px` }),
    };
}

function firstSelectableIndex() {
    const selectedIndex = filtered.value.findIndex((option) => sameValue(option.value, props.modelValue));
    if (selectedIndex >= 0) return selectedIndex;

    return filtered.value.findIndex((option) => !option.disabled);
}

async function openMenu() {
    if (props.disabled || open.value) return;

    open.value = true;
    search.value = '';
    await nextTick();
    positionMenu();
    highlighted.value = firstSelectableIndex();
    (showSearch.value ? searchInput.value : menu.value)?.focus();
    scrollHighlightedIntoView();
}

function closeMenu(refocus = false) {
    if (!open.value) return;

    open.value = false;
    search.value = '';
    highlighted.value = -1;
    if (refocus) trigger.value?.focus();
}

function toggleMenu() {
    open.value ? closeMenu(true) : openMenu();
}

function choose(option) {
    if (!option || option.disabled) return;

    emit('update:modelValue', option.value);
    emit('change', option.value, option.raw);
    closeMenu(true);
}

function clear() {
    emit('update:modelValue', '');
    emit('change', '', null);
}

function move(step) {
    const total = filtered.value.length;
    if (!total) return;

    let index = highlighted.value;
    for (let i = 0; i < total; i += 1) {
        index = (index + step + total) % total;
        if (!filtered.value[index].disabled) break;
    }

    highlighted.value = index;
    scrollHighlightedIntoView();
}

function scrollHighlightedIntoView() {
    nextTick(() => {
        menu.value?.querySelector('.ss-option--active')?.scrollIntoView({ block: 'nearest' });
    });
}

function onTriggerKeydown(event) {
    if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
        event.preventDefault();
        openMenu();
    }
}

function onMenuKeydown(event) {
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        move(1);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        move(-1);
    } else if (event.key === 'Enter') {
        event.preventDefault();
        choose(filtered.value[highlighted.value]);
    } else if (event.key === 'Escape' || event.key === 'Tab') {
        closeMenu(true);
    }
}

function onDocumentPointerDown(event) {
    if (root.value?.contains(event.target) || menu.value?.contains(event.target)) return;

    closeMenu();
}

// The menu is fixed to the viewport, so anything that moves the field has to
// move the menu with it.
function bindViewportListeners(active) {
    const method = active ? 'addEventListener' : 'removeEventListener';
    document[method]('pointerdown', onDocumentPointerDown, true);
    window[method]('scroll', positionMenu, true);
    window[method]('resize', positionMenu);
}

watch(open, (isOpen) => bindViewportListeners(isOpen));
watch(search, () => {
    highlighted.value = filtered.value.findIndex((option) => !option.disabled);
});

onBeforeUnmount(() => bindViewportListeners(false));
</script>

<template>
    <!--
        `is-invalid` is mirrored onto the root because Bootstrap shows an error
        with `.is-invalid ~ .invalid-feedback`, and the root is what sits next
        to that message now that the real control is nested.
    -->
    <div ref="root" class="ss-root" :class="{ 'is-invalid': invalid }">
        <button
            ref="trigger"
            type="button"
            class="ss-trigger"
            :class="[controlClass, { 'is-invalid': invalid, 'ss-trigger--open': open, 'ss-trigger--placeholder': !hasValue }]"
            :disabled="disabled"
            role="combobox"
            aria-haspopup="listbox"
            :aria-expanded="open"
            v-bind="$attrs"
            @click="toggleMenu"
            @keydown="onTriggerKeydown"
        >
            <span class="ss-value">{{ selected ? selected.label : placeholder }}</span>
            <span
                v-if="clearable && hasValue && !disabled"
                class="ss-clear"
                title="Clear"
                @click.stop="clear"
            >&times;</span>
            <span class="ss-caret"></span>
        </button>

        <!--
            A native select carries the `required` rule so the browser still
            blocks an empty submit and points at the right field. It is kept
            paintable (not display:none) or Chrome refuses to focus it.
        -->
        <select
            v-if="required"
            class="ss-validity"
            :name="name"
            :required="required"
            :disabled="disabled"
            tabindex="-1"
            :aria-label="placeholder"
            @focus="openMenu"
        >
            <option value="" :selected="!hasValue"></option>
            <option v-if="hasValue" :value="selected.value" selected></option>
        </select>

        <Teleport to="body">
            <div
                v-if="open"
                ref="menu"
                class="ss-menu"
                :style="menuStyle"
                tabindex="-1"
                @keydown="onMenuKeydown"
            >
                <div v-if="showSearch" class="ss-search">
                    <input
                        ref="searchInput"
                        v-model="search"
                        type="text"
                        class="ss-search-input"
                        :placeholder="searchPlaceholder"
                        autocomplete="off"
                        @keydown="onMenuKeydown"
                    >
                </div>
                <ul class="ss-list" role="listbox">
                    <li
                        v-for="(option, index) in filtered"
                        :key="`${option.value}-${index}`"
                        class="ss-option"
                        role="option"
                        :aria-selected="sameValue(option.value, modelValue)"
                        :class="{
                            'ss-option--active': index === highlighted,
                            'ss-option--selected': sameValue(option.value, modelValue),
                            'ss-option--disabled': option.disabled,
                        }"
                        @mouseenter="highlighted = index"
                        @click="choose(option)"
                    >
                        <span class="ss-option-label">{{ option.label }}</span>
                        <small v-if="option.hint" class="ss-option-hint">{{ option.hint }}</small>
                    </li>
                    <li v-if="!filtered.length" class="ss-empty">{{ emptyText }}</li>
                </ul>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
.ss-root {
    position: relative;
}

.ss-trigger {
    display: flex;
    align-items: center;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.ss-trigger:disabled {
    cursor: not-allowed;
    opacity: 0.65;
}

.ss-trigger--open {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
}

.ss-trigger--placeholder .ss-value {
    color: #869099;
}

.ss-value {
    flex: 1 1 auto;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ss-clear {
    flex: 0 0 auto;
    padding: 0 0.4rem;
    font-size: 1.1em;
    line-height: 1;
    color: #adb5bd;
}

.ss-clear:hover {
    color: #dc3545;
}

.ss-caret {
    flex: 0 0 auto;
    margin-left: 0.4rem;
    border-top: 5px solid currentColor;
    border-right: 4px solid transparent;
    border-left: 4px solid transparent;
    opacity: 0.5;
}

.ss-validity {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 1px;
    padding: 0;
    border: 0;
    opacity: 0;
    pointer-events: none;
}
</style>

<style>
/* Teleported to <body>, so these cannot be scoped to the component. */
.ss-menu {
    position: fixed;
    z-index: 2000;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #fff;
    border: 1px solid #dbe2ea;
    border-radius: 6px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
    outline: none;
}

.ss-search {
    flex: 0 0 auto;
    padding: 6px;
    border-bottom: 1px solid #eef2f7;
}

.ss-search-input {
    width: 100%;
    padding: 5px 10px;
    font-size: 0.875rem;
    color: #495057;
    background: #f8fafc;
    border: 1px solid #dbe2ea;
    border-radius: 4px;
    outline: none;
}

.ss-search-input:focus {
    background: #fff;
    border-color: #80bdff;
}

.ss-list {
    flex: 1 1 auto;
    margin: 0;
    padding: 4px 0;
    overflow-y: auto;
    list-style: none;
}

.ss-option {
    padding: 6px 12px;
    font-size: 0.875rem;
    line-height: 1.35;
    color: #37474f;
    cursor: pointer;
}

.ss-option--active {
    background: #eff6ff;
}

.ss-option--selected {
    font-weight: 600;
    color: #1d4ed8;
}

.ss-option--disabled {
    color: #b0bac3;
    cursor: not-allowed;
}

.ss-option--disabled.ss-option--active {
    background: transparent;
}

.ss-option-hint {
    display: block;
    font-size: 0.75rem;
    color: #8a97a4;
}

.ss-empty {
    padding: 10px 12px;
    font-size: 0.875rem;
    color: #8a97a4;
    text-align: center;
}
</style>
