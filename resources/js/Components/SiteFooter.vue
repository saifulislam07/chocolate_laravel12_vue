<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { PhoneIcon, EnvelopeIcon, MapPinIcon } from "@heroicons/vue/24/outline";
import { menuHref } from "@/composables/useMenuHref";

const page = usePage();
const settings = computed(() => page.props.webSettings || {});

const newsletterEmail = ref("");
const newsletterStatus = ref("");
function subscribeNewsletter() {
    if (!newsletterEmail.value) return;
    router.post(route("newsletter.subscribe"), { email: newsletterEmail.value }, {
        preserveScroll: true,
        onSuccess: () => {
            newsletterStatus.value = "success";
            newsletterEmail.value = "";
        },
        onError: () => {
            newsletterStatus.value = "error";
        },
    });
}
</script>

<template>
    <!-- ================= FOOTER ================= -->
    <footer class="relative -mt-px bg-cocov-brown text-white">
        <!-- subtle cocoa texture overlay -->
        <div class="pointer-events-none absolute inset-0 z-0 opacity-[0.10]" aria-hidden="true"
             style="background-image: url(&quot;data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='180' height='180' viewBox='0 0 180 180'><g fill='none' stroke='%23f7e6d0' stroke-width='2'><ellipse cx='40' cy='40' rx='18' ry='11' transform='rotate(-25 40 40)'/><path d='M28 44 L52 36'/><rect x='110' y='30' width='34' height='26' rx='3'/><path d='M110 43 h34 M121 30 v26 M133 30 v26'/><ellipse cx='150' cy='120' rx='16' ry='10' transform='rotate(30 150 120)'/><path d='M140 116 L160 124'/><circle cx='70' cy='130' r='4'/><circle cx='90' cy='120' r='3'/><rect x='20' y='120' width='30' height='22' rx='3' transform='rotate(-12 20 120)'/></g></svg>&quot;); background-size: 180px 180px;"></div>

        <div class="relative z-10">
            <div class="mx-auto grid max-w-[1350px] gap-12 px-5 pb-14 pt-7 md:grid-cols-2 md:px-8 lg:grid-cols-4 lg:px-[126px]">
                <!-- Sign up -->
                <div>
                    <h4 class="font-heading text-[24px] leading-[32px] text-white">Sign Up and Save</h4>
                    <p class="mt-6 max-w-[307px] text-[16px] leading-[25px] text-white/90">Subscribe to get special offers, free giveaways, &amp; once-in-a-lifetime deals.</p>
                    <form class="mt-6 max-w-[314px]" @submit.prevent="subscribeNewsletter">
                        <input v-model="newsletterEmail" type="email" required placeholder="Email here" class="w-full border-0 border-b border-cocov-gold bg-transparent px-1 py-2 text-[14px] text-cocov-gold placeholder:text-cocov-gold focus:border-cocov-gold focus:ring-0" />
                        <button type="submit" class="mt-6 flex h-[50px] w-[133px] items-center justify-center rounded-[3px] border border-cocov-gold text-[16px] text-cocov-gold transition hover:bg-cocov-gold hover:text-white">Submit</button>
                        <p v-if="newsletterStatus === 'success'" class="mt-3 text-[13px] text-white/90">Thanks — you're on the list!</p>
                        <p v-if="newsletterStatus === 'error'" class="mt-3 text-[13px] text-red-200">That didn't work — check the email and try again.</p>
                    </form>
                </div>
                <!-- Company -->
                <div>
                    <h4 class="font-heading text-[24px] leading-[32px] text-white">Company Information</h4>
                    <div class="mt-6 flex flex-col text-[16px] leading-[37px] text-white/90">
                        <Link :href="menuHref('about-us')" class="transition hover:text-cocov-gold">About Us</Link>
                        <Link :href="menuHref('employment')" class="transition hover:text-cocov-gold">Employment</Link>
                        <Link :href="menuHref('retail-store-locations')" class="transition hover:text-cocov-gold">Retail Store</Link>
                        <Link :href="menuHref('terms-of-service')" class="transition hover:text-cocov-gold">Terms of Service</Link>
                        <Link :href="menuHref('wholesale')" class="transition hover:text-cocov-gold">Wholesale</Link>
                    </div>
                </div>
                <!-- Useful -->
                <div>
                    <h4 class="font-heading text-[24px] leading-[32px] text-white">Useful Links</h4>
                    <div class="mt-6 flex flex-col text-[16px] leading-[37px] text-white/90">
                        <Link :href="route('products.index')" class="transition hover:text-cocov-gold">Products</Link>
                        <Link :href="menuHref('privacy-policy')" class="transition hover:text-cocov-gold">Privacy Policy</Link>
                        <Link :href="menuHref('refund-policy')" class="transition hover:text-cocov-gold">Refund and Returns</Link>
                        <Link :href="route('customer.dashboard')" class="transition hover:text-cocov-gold">Order Status</Link>
                        <Link :href="menuHref('become-an-affiliate')" class="transition hover:text-cocov-gold">Become an Affiliate</Link>
                    </div>
                </div>
                <!-- Contact -->
                <div>
                    <h4 class="font-heading text-[24px] leading-[32px] text-white">Contact Information</h4>
                    <div class="mt-6 flex flex-col gap-3 text-[16px] text-white/90">
                        <a :href="`tel:${settings.phone || '+8801886831130'}`" class="flex items-center gap-3 transition hover:text-cocov-gold"><PhoneIcon class="h-[18px] w-[18px] shrink-0 text-cocov-gold" />{{ settings.phone || '+88 01886 831 130' }}</a>
                        <a :href="`mailto:${settings.email || 'info@cococraft.com.bd'}`" class="flex items-center gap-3 transition hover:text-cocov-gold"><EnvelopeIcon class="h-[18px] w-[18px] shrink-0 text-cocov-gold" />{{ settings.email || 'info@cococraft.com.bd' }}</a>
                        <span class="flex items-start gap-3"><MapPinIcon class="mt-1 h-[18px] w-[18px] shrink-0 text-cocov-gold" />{{ settings.address || 'House 7, Road 4, Mirpur 7, Dhaka' }}</span>
                    </div>
                    <div class="mt-6 flex items-center gap-5 text-white">
                        <a :href="settings.facebook_url || '#'" aria-label="Facebook" class="transition hover:text-cocov-gold"><svg viewBox="0 0 24 24" class="h-6 w-6 fill-current"><path d="M13.5 21v-8h2.6l.4-3h-3V8.1c0-.9.3-1.5 1.6-1.5H17V4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H8.2v3h2.6v8h2.7z"/></svg></a>
                        <a :href="settings.twitter_url || '#'" aria-label="X" class="transition hover:text-cocov-gold"><svg viewBox="0 0 24 24" class="h-5 w-5 fill-current"><path d="M18.9 2H22l-7 8 8.2 12h-6.5l-5-6.6L5.9 22H2.8l7.5-8.6L2.4 2h6.7l4.6 6.1L18.9 2zm-1.1 18h1.7L7.3 3.8H5.5L17.8 20z"/></svg></a>
                        <a :href="settings.pinterest_url || '#'" aria-label="Pinterest" class="transition hover:text-cocov-gold"><svg viewBox="0 0 24 24" class="h-6 w-6 fill-current"><path d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.2-5s-.3-.6-.3-1.5c0-1.4.8-2.5 1.9-2.5.9 0 1.3.7 1.3 1.5 0 .9-.6 2.2-.9 3.5-.2 1 .5 1.9 1.6 1.9 1.9 0 3.2-2.4 3.2-5.3 0-2.2-1.5-3.8-4.1-3.8a4.7 4.7 0 0 0-4.9 4.7c0 .9.3 1.5.7 2 .2.2.2.3.1.6l-.2.9c-.1.3-.3.4-.6.2-1.2-.5-1.8-1.9-1.8-3.4 0-2.6 2.2-5.7 6.5-5.7 3.5 0 5.8 2.5 5.8 5.2 0 3.6-2 6.3-4.9 6.3-1 0-1.9-.5-2.2-1.1l-.6 2.4c-.2.8-.7 1.7-1.1 2.4A10 10 0 1 0 12 2z"/></svg></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-10 bg-cocov-brown-dark py-8 text-center text-[16px] text-white/90">
            Copyright © {{ new Date().getFullYear() }} {{ settings.site_name || 'Cococraft' }} - All Rights Reserved.
        </div>
    </footer>
</template>
