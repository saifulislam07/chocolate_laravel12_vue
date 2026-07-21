<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-12">
            <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.3em] text-cocov-gold">Customer Login</p>
            <h1 class="font-heading text-4xl text-cocov-text mb-4">Welcome Back</h1>
            <p class="text-sm text-cocov-text/60 leading-relaxed">
                Enter your credentials to access your Coco Craft account and continue your journey of taste.
            </p>
        </div>

        <div v-if="status" class="mb-6 border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="group">
                <InputLabel for="email" value="Email Address" class="mb-2" />

                <TextInput
                    id="email"
                    type="email"
                    class="block w-full transition-all duration-300"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="email@example.com"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="group mt-4">
                <div class="flex items-center justify-between mb-2">
                    <InputLabel for="password" value="Password" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[10px] uppercase tracking-widest text-cocov-gold hover:text-[#e0851a] transition-colors font-bold"
                    >
                        Forgot password?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="block w-full transition-all duration-300"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex items-center">
                <label class="flex items-center group cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-3 text-[10px] uppercase tracking-[0.2em] text-cocov-text/40 group-hover:text-cocov-text transition-colors font-bold"
                        >Keep me signed in</span
                    >
                </label>
            </div>

            <div class="mt-8">
                <PrimaryButton
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Verifying...</span>
                    <span v-else>Sign In</span>
                </PrimaryButton>
            </div>
            
            <p class="mt-8 text-center text-[10px] uppercase tracking-[0.2em] text-cocov-text/40">
                Don't have an account? 
                <Link :href="route('register')" class="text-cocov-gold hover:text-[#e0851a] font-bold ml-1 transition-colors">Join the family</Link>
            </p>

            <p class="mt-5 text-center text-[10px] uppercase tracking-[0.2em] text-cocov-text/35">
                Admin?
                <Link :href="route('admin.login')" class="text-cocov-text hover:text-cocov-gold font-bold ml-1 transition-colors">Use admin login</Link>
            </p>
        </form>
    </GuestLayout>
</template>
