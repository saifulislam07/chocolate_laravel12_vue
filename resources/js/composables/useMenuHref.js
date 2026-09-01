export function menuHref(url) {
    if (!url) {
        return "#";
    }

    // A bare value is a CMS page slug, which lives at the root: /about-us
    return url.startsWith("http") || url.startsWith("/") ? url : `/${url}`;
}
