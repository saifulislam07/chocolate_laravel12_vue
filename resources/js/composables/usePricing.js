/**
 * The Product model casts prices with `decimal:2`, so they arrive in the page
 * props as strings: "950.00", "1050.00". Comparing those with `>` compares them
 * character by character, and "1050.00" > "950.00" is false because '1' sorts
 * before '9' -- which silently hid the discount on anything whose old price
 * crossed into an extra digit.
 *
 * Every discount check goes through here so the comparison is always numeric.
 */
export function isDiscounted(product) {
    return Number(product?.compare_at_price || 0) > Number(product?.price || 0);
}

/**
 * How much has been knocked off, as a whole percentage. Zero when there is no
 * discount, so a caller can use it as the condition too.
 */
export function discountPercent(product) {
    if (!isDiscounted(product)) {
        return 0;
    }

    const compareAt = Number(product.compare_at_price);

    return Math.round(((compareAt - Number(product.price)) / compareAt) * 100);
}
