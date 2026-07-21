<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="mb-12">
            <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.3em] text-cocov-gold">Join Coco Craft</p>
            <h1 class="mb-4 font-heading text-4xl text-cocov-text">Create Account</h1>
            <p class="text-sm leading-relaxed text-cocov-text/60">
                Sign up to save favorites, track orders, and get a bite of love delivered to your door.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="group">
                <InputLabel for="name" value="Name" class="mb-2" />

                <TextInput
                    id="name"
                    type="text"
                    class="block w-full transition-all duration-300"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="group">
                <InputLabel for="email" value="Email Address" class="mb-2" />

                <TextInput
                    id="email"
                    type="email"
                    class="block w-full transition-all duration-300"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="email@example.com"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="group">
                <InputLabel for="password" value="Password" class="mb-2" />

                <TextInput
                    id="password"
                    type="password"
                    class="block w-full transition-all duration-300"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="group">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                    class="mb-2"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="block w-full transition-all duration-300"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-8">
                <PrimaryButton
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Creating Account...</span>
                    <span v-else>Register</span>
                </PrimaryButton>
            </div>

            <p class="mt-8 text-center text-[10px] uppercase tracking-[0.2em] text-cocov-text/40">
                Already have an account?
                <Link :href="route('login')" class="ml-1 font-bold text-cocov-gold transition-colors hover:text-[#e0851a]">Sign in</Link>
            </p>
        </form>
    </GuestLayout>
</template>
