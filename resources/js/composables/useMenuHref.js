export function menuHref(url) {
    if (!url) {
        return "#";
    }

    return url.startsWith("http") || url.startsWith("/") ? url : `/p/${url}`;
}
