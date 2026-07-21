<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <MainLayout>
        <Head title="My Profile" />

        <div class="bg-white">
            <section class="border-b border-[#eee4d8] bg-[#fcf8f3] py-14">
                <div class="mx-auto max-w-screen-2xl px-6">
                    <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-cocov-gold">My Account</p>
                    <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <h1 class="font-heading text-5xl uppercase tracking-tight text-cocov-text">My Profile</h1>
                        <Link :href="route('customer.dashboard')" class="inline-flex rounded-[3px] border border-cocov-text/20 px-8 py-4 text-[11px] font-bold uppercase tracking-[0.22em] text-cocov-text transition hover:border-cocov-gold hover:text-cocov-gold">
                            Back to Dashboard
                        </Link>
                    </div>
                </div>
            </section>

            <main class="mx-auto max-w-3xl px-6 py-12 space-y-8">
                <div class="border border-gray-100 p-6 sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="border border-gray-100 p-6 sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="border border-gray-100 p-6 sm:p-8">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </main>
        </div>
    </MainLayout>
</template>
