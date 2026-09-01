<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Write something...' },
    height: { type: [String, Number], default: 200 },
    invalid: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    // 'full' shows every control, 'basic' keeps inline formatting + lists + links only
    toolbar: { type: String, default: 'full' },
});

const emit = defineEmits(['update:modelValue']);

const editorRef = ref(null);
const sourceMode = ref(false);
const sourceHtml = ref('');
const activeStates = ref({});
const lastEmitted = ref(props.modelValue || '');

const isFull = computed(() => props.toolbar === 'full');
const editorHeight = computed(() =>
    typeof props.height === 'number' ? props.height + 'px' : props.height
);
const isEmpty = computed(() => !stripToText(props.modelValue));

const INLINE_COMMANDS = [
    'bold', 'italic', 'underline', 'strikeThrough',
    'insertUnorderedList', 'insertOrderedList',
    'justifyLeft', 'justifyCenter', 'justifyRight',
];

const ALLOWED_TAGS = new Set([
    'P', 'BR', 'DIV', 'SPAN', 'B', 'STRONG', 'I', 'EM', 'U', 'S', 'STRIKE', 'SUB', 'SUP',
    'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'UL', 'OL', 'LI', 'BLOCKQUOTE', 'PRE', 'CODE',
    'A', 'IMG', 'HR', 'TABLE', 'THEAD', 'TBODY', 'TR', 'TH', 'TD',
]);

const ALLOWED_ATTRS = { A: ['href', 'target', 'rel'], IMG: ['src', 'alt', 'title'] };

function stripToText(html) {
    if (!html) return '';
    return String(html)
        .replace(/<[^>]*>/g, '')
        .replace(/&nbsp;/gi, ' ')
        .trim();
}

// Keeps pasted markup usable: drops scripts, styles, classes and stray inline attributes.
function sanitize(html) {
    const holder = document.createElement('div');
    holder.innerHTML = html || '';

    const walk = (node) => {
        [...node.children].forEach((child) => {
            if (!ALLOWED_TAGS.has(child.tagName)) {
                child.replaceWith(document.createTextNode(child.textContent || ''));
                return;
            }

            const allowed = ALLOWED_ATTRS[child.tagName] || [];
            [...child.attributes].forEach((attr) => {
                if (!allowed.includes(attr.name.toLowerCase())) {
                    child.removeAttribute(attr.name);
                }
            });

            if (child.tagName === 'A') {
                if (/^\s*javascript:/i.test(child.getAttribute('href') || '')) {
                    child.removeAttribute('href');
                }
                child.setAttribute('target', '_blank');
                child.setAttribute('rel', 'noopener noreferrer');
            }

            if (child.tagName === 'IMG' && /^\s*javascript:/i.test(child.getAttribute('src') || '')) {
                child.remove();
                return;
            }

            walk(child);
        });
    };

    walk(holder);
    return holder.innerHTML;
}

function normalize(html) {
    const clean = (html || '').trim();
    if (!clean || clean === '<br>' || clean === '<p><br></p>' || clean === '<div><br></div>') {
        return '';
    }
    return clean;
}

function pushValue(html) {
    const value = normalize(html);
    lastEmitted.value = value;
    emit('update:modelValue', value);
}

function syncFromEditor() {
    if (!editorRef.value) return;
    pushValue(editorRef.value.innerHTML);
}

function refreshStates() {
    if (!editorRef.value || sourceMode.value) return;
    const next = {};
    INLINE_COMMANDS.forEach((cmd) => {
        try {
            next[cmd] = document.queryCommandState(cmd);
        } catch (e) {
            next[cmd] = false;
        }
    });
    activeStates.value = next;
}

function onSelectionChange() {
    if (!editorRef.value) return;
    const selection = document.getSelection();
    if (selection && selection.anchorNode && editorRef.value.contains(selection.anchorNode)) {
        refreshStates();
    }
}

function exec(command, value = null) {
    if (props.disabled || sourceMode.value) return;
    editorRef.value?.focus();
    document.execCommand(command, false, value);
    syncFromEditor();
    refreshStates();
}

function applyBlock(event) {
    const tag = event.target.value;
    event.target.value = '';
    if (!tag) return;
    exec('formatBlock', '<' + tag + '>');
}

function insertLink() {
    const url = window.prompt('Link URL', 'https://');
    if (!url) return;
    exec('createLink', url);
    editorRef.value?.querySelectorAll('a[href]').forEach((anchor) => {
        anchor.setAttribute('target', '_blank');
        anchor.setAttribute('rel', 'noopener noreferrer');
    });
    syncFromEditor();
}

function insertImage() {
    const url = window.prompt('Image URL', 'https://');
    if (!url) return;
    exec('insertImage', url);
}

function clearFormatting() {
    exec('removeFormat');
    exec('formatBlock', '<p>');
}

function onPaste(event) {
    if (props.disabled) return;
    event.preventDefault();

    const clipboard = event.clipboardData;
    const html = clipboard?.getData('text/html');
    const text = clipboard?.getData('text/plain') || '';

    if (html) {
        document.execCommand('insertHTML', false, sanitize(html));
    } else {
        document.execCommand('insertText', false, text);
    }
    syncFromEditor();
}

function toggleSource() {
    if (sourceMode.value) {
        pushValue(sanitize(sourceHtml.value));
        sourceMode.value = false;
        nextTick(() => {
            if (editorRef.value) editorRef.value.innerHTML = props.modelValue || '';
        });
        return;
    }

    sourceHtml.value = props.modelValue || '';
    sourceMode.value = true;
}

function onSourceInput() {
    pushValue(sourceHtml.value);
}

watch(
    () => props.modelValue,
    (value) => {
        const incoming = value || '';

        if (sourceMode.value) {
            if (incoming !== sourceHtml.value) sourceHtml.value = incoming;
            return;
        }

        // Repaint only when the change came from outside (form reset, modal reuse, prop load),
        // otherwise typing would lose the caret position on every keystroke.
        if (editorRef.value && incoming !== lastEmitted.value && incoming !== editorRef.value.innerHTML) {
            editorRef.value.innerHTML = incoming;
            lastEmitted.value = incoming;
        }
    }
);

onMounted(() => {
    try {
        document.execCommand('defaultParagraphSeparator', false, 'p');
    } catch (e) {
        // Older engines keep their own separator; harmless.
    }
    if (editorRef.value) editorRef.value.innerHTML = props.modelValue || '';
    document.addEventListener('selectionchange', onSelectionChange);
});

onBeforeUnmount(() => {
    document.removeEventListener('selectionchange', onSelectionChange);
});
</script>

<template>
    <div class="rte" :class="{ 'rte-invalid': invalid, 'rte-disabled': disabled }">
        <div class="rte-toolbar">
            <div class="rte-group">
                <button type="button" class="rte-btn" :class="{ active: activeStates.bold }" title="Bold" @click="exec('bold')"><i class="fas fa-bold"></i></button>
                <button type="button" class="rte-btn" :class="{ active: activeStates.italic }" title="Italic" @click="exec('italic')"><i class="fas fa-italic"></i></button>
                <button type="button" class="rte-btn" :class="{ active: activeStates.underline }" title="Underline" @click="exec('underline')"><i class="fas fa-underline"></i></button>
                <button type="button" class="rte-btn" :class="{ active: activeStates.strikeThrough }" title="Strikethrough" @click="exec('strikeThrough')"><i class="fas fa-strikethrough"></i></button>
            </div>

            <div v-if="isFull" class="rte-group">
                <select class="rte-select" title="Text style" @change="applyBlock">
                    <option value="">Format</option>
                    <option value="p">Paragraph</option>
                    <option value="h2">Heading 2</option>
                    <option value="h3">Heading 3</option>
                    <option value="h4">Heading 4</option>
                    <option value="blockquote">Quote</option>
                    <option value="pre">Code block</option>
                </select>
            </div>

            <div class="rte-group">
                <button type="button" class="rte-btn" :class="{ active: activeStates.insertUnorderedList }" title="Bullet list" @click="exec('insertUnorderedList')"><i class="fas fa-list-ul"></i></button>
                <button type="button" class="rte-btn" :class="{ active: activeStates.insertOrderedList }" title="Numbered list" @click="exec('insertOrderedList')"><i class="fas fa-list-ol"></i></button>
            </div>

            <div v-if="isFull" class="rte-group">
                <button type="button" class="rte-btn" :class="{ active: activeStates.justifyLeft }" title="Align left" @click="exec('justifyLeft')"><i class="fas fa-align-left"></i></button>
                <button type="button" class="rte-btn" :class="{ active: activeStates.justifyCenter }" title="Align center" @click="exec('justifyCenter')"><i class="fas fa-align-center"></i></button>
                <button type="button" class="rte-btn" :class="{ active: activeStates.justifyRight }" title="Align right" @click="exec('justifyRight')"><i class="fas fa-align-right"></i></button>
            </div>

            <div class="rte-group">
                <button type="button" class="rte-btn" title="Insert link" @click="insertLink"><i class="fas fa-link"></i></button>
                <button type="button" class="rte-btn" title="Remove link" @click="exec('unlink')"><i class="fas fa-unlink"></i></button>
                <button v-if="isFull" type="button" class="rte-btn" title="Insert image by URL" @click="insertImage"><i class="fas fa-image"></i></button>
                <button v-if="isFull" type="button" class="rte-btn" title="Horizontal line" @click="exec('insertHorizontalRule')"><i class="fas fa-minus"></i></button>
            </div>

            <div class="rte-group">
                <button type="button" class="rte-btn" title="Undo" @click="exec('undo')"><i class="fas fa-rotate-left"></i></button>
                <button type="button" class="rte-btn" title="Redo" @click="exec('redo')"><i class="fas fa-rotate-right"></i></button>
                <button type="button" class="rte-btn" title="Clear formatting" @click="clearFormatting"><i class="fas fa-eraser"></i></button>
            </div>

            <div v-if="isFull" class="rte-group rte-group-end">
                <button type="button" class="rte-btn" :class="{ active: sourceMode }" title="HTML source" @click="toggleSource"><i class="fas fa-code"></i></button>
            </div>
        </div>

        <textarea
            v-if="sourceMode"
            v-model="sourceHtml"
            class="rte-source"
            :style="{ minHeight: editorHeight }"
            :disabled="disabled"
            spellcheck="false"
            @input="onSourceInput"
        ></textarea>

        <div
            v-show="!sourceMode"
            ref="editorRef"
            class="rte-content"
            :class="{ 'rte-empty': isEmpty }"
            :style="{ minHeight: editorHeight }"
            :contenteditable="!disabled"
            :data-placeholder="placeholder"
            spellcheck="true"
            @input="syncFromEditor"
            @blur="syncFromEditor"
            @paste="onPaste"
            @keyup="refreshStates"
            @mouseup="refreshStates"
        ></div>
    </div>
</template>

<style scoped>
.rte {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    overflow: hidden;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.rte:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

.rte-invalid {
    border-color: #dc3545;
}

.rte-invalid:focus-within {
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.12);
}

.rte-disabled {
    background: #f8fafc;
    opacity: 0.75;
}

.rte-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 2px;
    padding: 6px 8px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.rte-group {
    display: flex;
    align-items: center;
    gap: 2px;
    padding-right: 6px;
    margin-right: 4px;
    border-right: 1px solid #e2e8f0;
}

.rte-group:last-child,
.rte-group-end {
    border-right: 0;
    margin-right: 0;
    padding-right: 0;
}

.rte-group-end {
    margin-left: auto;
}

.rte-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: 0;
    border-radius: 6px;
    background: transparent;
    color: #475569;
    font-size: 12px;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}

.rte-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.rte-btn.active {
    background: #6366f1;
    color: #fff;
}

.rte-select {
    height: 28px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #fff;
    color: #475569;
    font-size: 12px;
    padding: 0 6px;
    cursor: pointer;
}

.rte-content {
    padding: 12px 14px;
    outline: none;
    overflow-y: auto;
    max-height: 520px;
    font-size: 14px;
    line-height: 1.7;
    color: #334155;
    word-break: break-word;
}

.rte-empty::before {
    content: attr(data-placeholder);
    color: #94a3b8;
    pointer-events: none;
    display: block;
}

.rte-source {
    display: block;
    width: 100%;
    border: 0;
    outline: none;
    padding: 12px 14px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 12.5px;
    line-height: 1.6;
    color: #0f172a;
    background: #f8fafc;
    resize: vertical;
}

.rte-content :deep(p) { margin: 0 0 0.75rem; }
.rte-content :deep(p:last-child) { margin-bottom: 0; }
.rte-content :deep(h1),
.rte-content :deep(h2),
.rte-content :deep(h3),
.rte-content :deep(h4) { margin: 0 0 0.6rem; font-weight: 700; color: #0f172a; line-height: 1.3; }
.rte-content :deep(h2) { font-size: 1.35rem; }
.rte-content :deep(h3) { font-size: 1.15rem; }
.rte-content :deep(h4) { font-size: 1rem; }
.rte-content :deep(ul),
.rte-content :deep(ol) { margin: 0 0 0.75rem; padding-left: 1.4rem; }
.rte-content :deep(li) { margin-bottom: 0.25rem; }
.rte-content :deep(blockquote) {
    margin: 0 0 0.75rem;
    padding: 0.5rem 0.9rem;
    border-left: 3px solid #6366f1;
    background: #f5f3ff;
    color: #475569;
}
.rte-content :deep(pre) {
    margin: 0 0 0.75rem;
    padding: 0.7rem 0.9rem;
    background: #0f172a;
    color: #e2e8f0;
    border-radius: 6px;
    font-size: 12.5px;
    overflow-x: auto;
}
.rte-content :deep(a) { color: #4f46e5; text-decoration: underline; }
.rte-content :deep(img) { max-width: 100%; height: auto; border-radius: 6px; }
.rte-content :deep(hr) { border: 0; border-top: 1px solid #e2e8f0; margin: 1rem 0; }
.rte-content :deep(table) { width: 100%; border-collapse: collapse; margin-bottom: 0.75rem; }
.rte-content :deep(th),
.rte-content :deep(td) { border: 1px solid #e2e8f0; padding: 6px 8px; }
</style>
