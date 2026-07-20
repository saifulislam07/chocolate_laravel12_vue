<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { XMarkIcon, CheckCircleIcon } from "@heroicons/vue/24/outline";
import SiteHeader from "@/Components/SiteHeader.vue";
import SiteFooter from "@/Components/SiteFooter.vue";
import BackToTop from "@/Components/BackToTop.vue";

const page = usePage();
const showToast = ref(false);
const toastMessage = ref("");
const isMessengerOpen = ref(false);

const flash = computed(() => page.props.flash || {});
const messengerSettings = computed(() => page.props.webSettings || {});
const shouldShowMessenger = computed(() => Boolean(
    messengerSettings.value?.messenger_enabled && messengerSettings.value?.messenger_page_id
));
const messengerLink = computed(() => `https://m.me/${messengerSettings.value.messenger_page_id}`);

watch(() => flash.value.success, (message) => {
    if (message) {
        toastMessage.value = message;
        showToast.value = true;
        setTimeout(() => {
            showToast.value = false;
        }, 3000);
    }
}, { immediate: true });

watch(shouldShowMessenger, (visible) => {
    if (!visible) {
        isMessengerOpen.value = false;
    }
});
</script>

<template>
    <div class="min-h-screen bg-white font-body text-cocov-text antialiased">
        <Head :title="(page.props.webSettings?.site_name || 'CocoCraft') + ' | A Bite of Love'">
            <link v-if="page.props.webSettings?.favicon" rel="icon" :href="page.props.webSettings.favicon">
        </Head>

        <!-- Toast Notification -->
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showToast" class="fixed top-24 right-6 z-[100] w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-2xl ring-1 ring-black ring-opacity-5">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <CheckCircleIcon class="h-6 w-6 text-green-400" aria-hidden="true" />
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900">{{ toastMessage }}</p>
                        </div>
                        <div class="ml-4 flex flex-shrink-0">
                            <button type="button" @click="showToast = false" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none">
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <SiteHeader />

        <main>
            <slot />
        </main>

        <BackToTop />

        <div v-if="shouldShowMessenger" class="fixed bottom-6 right-6 z-[90] flex flex-col items-end gap-4">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="translate-y-3 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-3 opacity-0"
            >
                <div v-if="isMessengerOpen" class="w-[330px] overflow-hidden rounded-2xl bg-white text-cocov-text shadow-2xl ring-1 ring-black/5 sm:w-[360px]">
                    <div class="flex items-center gap-4 border-b border-gray-100 px-5 py-4">
                        <div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-full bg-cocov-offer p-1.5">
                            <img :src="page.props.webSettings?.logo || '/images/cococraft-v2/logo.png'" :alt="page.props.webSettings?.site_name || 'CocoCraft'" class="h-full w-full rounded-full object-contain" />
                            <span class="absolute bottom-1 right-1 h-3 w-3 rounded-full border-2 border-white bg-emerald-400"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold leading-5">{{ page.props.webSettings?.site_name || 'CocoCraft' }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">Online</p>
                        </div>
                        <button type="button" class="text-gray-400 transition hover:text-cocov-text" aria-label="Close Messenger chat" @click="isMessengerOpen = false">
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="min-h-[145px] px-5 py-4">
                        <p class="mb-4 text-center text-[11px] text-gray-300">{{ new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</p>
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 shrink-0 overflow-hidden rounded-full bg-cocov-offer p-1">
                                <img :src="page.props.webSettings?.logo || '/images/cococraft-v2/logo.png'" :alt="page.props.webSettings?.site_name || 'CocoCraft'" class="h-full w-full rounded-full object-contain" />
                            </div>
                            <div class="rounded-full bg-gray-100 px-4 py-2 text-sm text-gray-700">
                                {{ messengerSettings.messenger_logged_in_greeting || 'Hi! How can we help you?' }}
                            </div>
                        </div>
                    </div>

                    <div class="px-5 pb-5 text-center">
                        <a
                            :href="messengerLink"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl"
                            :style="{ backgroundColor: messengerSettings.messenger_theme_color || '#F69521' }"
                        >
                            <svg viewBox="0 0 36 36" class="h-5 w-5 fill-current" aria-hidden="true">
                                <path d="M18 3.2C9.5 3.2 3 9.4 3 17.7c0 4.3 1.8 8 4.7 10.5v4.6l4.4-2.4c1.8.6 3.8.9 5.9.9 8.5 0 15-6.2 15-14.5S26.5 3.2 18 3.2Zm1.6 19.4-3.8-4-7.4 4 8.1-8.6 3.9 4 7.3-4-8.1 8.6Z" />
                            </svg>
                            Chat on Facebook
                        </a>
                    </div>
                </div>
            </Transition>

            <button
                type="button"
                class="relative flex h-16 w-16 items-center justify-center rounded-full text-white shadow-2xl transition hover:-translate-y-0.5"
                :style="{ backgroundColor: messengerSettings.messenger_theme_color || '#F69521' }"
                aria-label="Open Messenger chat"
                @click="isMessengerOpen = !isMessengerOpen"
            >
                <span class="absolute right-0 top-0 h-3.5 w-3.5 rounded-full border-2 border-white bg-red-500"></span>
                <svg viewBox="0 0 36 36" class="h-8 w-8 fill-current" aria-hidden="true">
                    <path d="M18 3.2C9.5 3.2 3 9.4 3 17.7c0 4.3 1.8 8 4.7 10.5v4.6l4.4-2.4c1.8.6 3.8.9 5.9.9 8.5 0 15-6.2 15-14.5S26.5 3.2 18 3.2Zm1.6 19.4-3.8-4-7.4 4 8.1-8.6 3.9 4 7.3-4-8.1 8.6Z" />
                </svg>
            </button>
        </div>

        <SiteFooter />
    </div>
</template>
