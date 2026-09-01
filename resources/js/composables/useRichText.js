/**
 * Helpers for the HTML produced by Components/RichTextEditor.vue.
 *
 * Rich fields are stored as HTML, so list/table cells that need a single compact
 * line (or a title attribute) should render the stripped text instead of markup.
 */

const BLOCK_BREAK = /<\/(p|div|li|h[1-6]|blockquote|tr)>/gi;
const LINE_BREAK = /<br\s*\/?>/gi;
const TAG = /<[^>]*>/g;

export function stripHtml(html) {
    if (html === null || html === undefined) {
        return "";
    }

    const text = String(html)
        .replace(LINE_BREAK, " ")
        .replace(BLOCK_BREAK, " ")
        .replace(TAG, "");

    const decoder = document.createElement("textarea");
    decoder.innerHTML = text;

    return decoder.value.replace(/\s+/g, " ").trim();
}

export function excerpt(html, length = 140) {
    const text = stripHtml(html);

    return text.length > length ? `${text.slice(0, length).trimEnd()}…` : text;
}

export function useRichText() {
    return { stripHtml, excerpt };
}
