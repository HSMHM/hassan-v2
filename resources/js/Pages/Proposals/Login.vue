<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    meta: Object,
});

const page = usePage();
const locale = computed(() => page.props.locale);

const form = useForm({
    proposal_id: '',
    password: '',
});

const submit = () => {
    const route = locale.value === 'en' ? '/en/proposals/verify' : '/proposals/verify';
    form.post(route, { preserveScroll: true });
};
</script>

<template>
    <MainLayout :meta="meta">
        <section class="section">
            <div class="container">
                <div class="proposal-login">
                    <h1 class="proposal-login__title">
                        {{ locale === 'ar' ? 'الدخول إلى العرض' : 'Proposal Access' }}
                    </h1>

                    <form class="form" @submit.prevent="submit" novalidate>
                        <div class="form__group">
                            <label class="form__label" for="proposal_id">
                                {{ locale === 'ar' ? 'رقم العرض' : 'Proposal ID' }}
                            </label>
                            <input
                                id="proposal_id"
                                v-model="form.proposal_id"
                                type="text"
                                class="form__input"
                                required
                                autocomplete="off"
                            />
                            <span v-if="form.errors.proposal_id" class="form__error">{{ form.errors.proposal_id }}</span>
                        </div>

                        <div class="form__group">
                            <label class="form__label" for="password">
                                {{ locale === 'ar' ? 'كلمة المرور' : 'Password' }}
                            </label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="form__input"
                                required
                                autocomplete="current-password"
                            />
                            <span v-if="form.errors.password" class="form__error">{{ form.errors.password }}</span>
                        </div>

                        <button type="submit" class="btn btn--primary" :disabled="form.processing">
                            {{ form.processing
                                ? (locale === 'ar' ? 'جارٍ التحقق...' : 'Verifying...')
                                : (locale === 'ar' ? 'دخول' : 'Sign in') }}
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
