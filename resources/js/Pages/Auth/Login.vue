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
            <h1 class="font-serif text-4xl text-godiva-charcoal mb-4">Welcome Back</h1>
            <p class="text-sm text-godiva-charcoal/60 leading-relaxed font-sans">
                Enter your credentials to access your SweetChocholate account and continue your journey of taste.
            </p>
        </div>

        <div v-if="status" class="mb-6 px-4 py-3 bg-green-50 text-sm font-medium text-green-600 border border-green-100 italic">
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
                        class="text-[10px] uppercase tracking-widest text-godiva-gold hover:text-godiva-gold-dark transition-colors font-bold"
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
                    <span class="ms-3 text-[10px] uppercase tracking-[0.2em] text-godiva-charcoal/40 group-hover:text-godiva-charcoal transition-colors font-bold"
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
            
            <p class="mt-8 text-center text-[10px] uppercase tracking-[0.2em] text-godiva-charcoal/40">
                Don't have an account? 
                <Link :href="route('register')" class="text-godiva-gold hover:text-godiva-gold-dark font-bold ml-1 transition-colors">Join the family</Link>
            </p>
        </form>
    </GuestLayout>
</template>
