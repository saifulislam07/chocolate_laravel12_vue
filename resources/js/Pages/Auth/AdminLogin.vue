<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: true,
});

function submit() {
    form.post(route('admin.login.store'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Admin Login" />

    <main class="min-h-screen bg-[#101010] text-white">
        <div class="grid min-h-screen lg:grid-cols-[0.95fr_1.05fr]">
            <section class="relative hidden overflow-hidden lg:block">
                <img src="/images/godiva/auth_bg.png" alt="Chocolate workspace" class="absolute inset-0 h-full w-full object-cover opacity-45" />
                <div class="absolute inset-0 bg-gradient-to-br from-black via-black/70 to-[#3b2418]/70"></div>
                <div class="relative z-10 flex h-full flex-col justify-between p-12">
                    <Link href="/" class="inline-flex items-center gap-3">
                        <img src="/images/godiva/logo-cute.png" alt="SweetChocholate" class="h-12 w-12 object-contain" />
                        <span class="font-serif text-2xl font-bold">SweetChocholate</span>
                    </Link>
                    <div class="max-w-lg">
                        <p class="text-[11px] font-bold uppercase tracking-[0.35em] text-godiva-gold">Control Room</p>
                        <h1 class="mt-5 font-serif text-6xl leading-none">Admin dashboard access</h1>
                        <p class="mt-6 text-sm leading-8 text-white/65">Manage products, orders, stock, menus, reports, and storefront settings from one focused workspace.</p>
                    </div>
                </div>
            </section>

            <section class="flex items-center justify-center px-6 py-12">
                <div class="w-full max-w-md">
                    <Link href="/" class="mb-10 inline-flex items-center gap-3 lg:hidden">
                        <img src="/images/godiva/logo-cute.png" alt="SweetChocholate" class="h-11 w-11 object-contain" />
                        <span class="font-serif text-2xl font-bold">SweetChocholate</span>
                    </Link>

                    <div class="border border-white/10 bg-white/[0.04] p-8 shadow-2xl backdrop-blur">
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-godiva-gold">Admin Login</p>
                        <h2 class="mt-4 font-serif text-4xl">Welcome back</h2>
                        <p class="mt-3 text-sm leading-7 text-white/55">Use an authorized admin account to continue.</p>

                        <div v-if="status" class="mt-6 border border-green-400/20 bg-green-400/10 px-4 py-3 text-sm text-green-200">
                            {{ status }}
                        </div>

                        <form class="mt-8 space-y-5" @submit.prevent="submit">
                            <div>
                                <label for="email" class="text-[10px] font-bold uppercase tracking-[0.22em] text-white/55">Email</label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    class="mt-2 h-12 w-full border border-white/10 bg-black/25 px-4 text-sm text-white placeholder:text-white/25 focus:border-godiva-gold focus:ring-0"
                                    placeholder="admin@example.com"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div>
                                <div class="flex items-center justify-between">
                                    <label for="password" class="text-[10px] font-bold uppercase tracking-[0.22em] text-white/55">Password</label>
                                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-[10px] font-bold uppercase tracking-widest text-godiva-gold">Reset</Link>
                                </div>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    class="mt-2 h-12 w-full border border-white/10 bg-black/25 px-4 text-sm text-white placeholder:text-white/25 focus:border-godiva-gold focus:ring-0"
                                    placeholder="Password"
                                />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <button
                                type="submit"
                                class="h-12 w-full bg-godiva-gold text-[11px] font-bold uppercase tracking-[0.25em] text-black transition hover:bg-white disabled:opacity-60"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Checking...' : 'Enter Admin' }}
                            </button>
                        </form>

                        <Link :href="route('login')" class="mt-8 block text-center text-[10px] font-bold uppercase tracking-[0.25em] text-white/35 transition hover:text-white">
                            Customer login
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>
