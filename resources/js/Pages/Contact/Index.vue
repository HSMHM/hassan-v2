<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import ContactCard from '@/Components/ContactCard.vue';

defineProps({
    meta: Object,
    breadcrumbs: Array,
});

const page = usePage();
const locale = computed(() => page.props.locale);
const translations = computed(() => page.props.translations || {});
const settings = computed(() => page.props.settings || {});

const t = (path) => {
    const parts = path.split('.');
    let cur = translations.value;
    for (const p of parts) {
        if (cur && typeof cur === 'object' && p in cur) {
            cur = cur[p];
        } else {
            return path;
        }
    }
    return cur;
};

const flashSuccess = computed(() => page.props.flash?.success);

const form = useForm({
    name: '',
    email: '',
    mobile: '',
    message: '',
    website: '',
});

const submit = () => {
    const url = locale.value === 'en' ? '/en/contact' : '/contact';
    form.post(url, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <MainLayout :meta="meta" :breadcrumbs="breadcrumbs">
        <section class="contact-section">
            <div class="container">
                <h1>{{ locale === 'ar' ? 'تواصل معي' : 'Contact' }}</h1>

                <div class="contact-grid mt-4">
                    <div class="contact-info">
                        <ContactCard
                            v-if="settings.whatsapp_url"
                            icon="fa-brands fa-whatsapp"
                            :label="locale === 'ar' ? 'واتساب' : 'WhatsApp'"
                            :value="settings.phone || settings.whatsapp_number"
                            :href="settings.whatsapp_url"
                        />
                        <ContactCard
                            v-if="settings.email"
                            icon="fa-solid fa-envelope"
                            :label="locale === 'ar' ? 'البريد الإلكتروني' : 'Email'"
                            :value="settings.email"
                            :href="`mailto:${settings.email}`"
                        />
                        <ContactCard
                            v-if="settings.phone"
                            icon="fa-solid fa-phone"
                            :label="locale === 'ar' ? 'الجوال' : 'Phone'"
                            :value="settings.phone"
                            :href="`tel:${settings.phone.replace(/\s/g, '')}`"
                        />
                        <ContactCard
                            v-if="settings.twitter_url"
                            icon="fa-brands fa-x-twitter"
                            label="X / Twitter"
                            :value="settings.twitter_handle || 'X'"
                            :href="settings.twitter_url"
                        />
                        <ContactCard
                            v-if="settings.linkedin_url"
                            icon="fa-brands fa-linkedin-in"
                            label="LinkedIn"
                            :value="settings.owner_name || 'LinkedIn'"
                            :href="settings.linkedin_url"
                        />
                        <ContactCard
                            v-if="settings.snapchat_url"
                            icon="fa-brands fa-snapchat"
                            label="Snapchat"
                            :value="settings.snapchat_handle || 'Snapchat'"
                            :href="settings.snapchat_url"
                        />
                    </div>

                    <div class="contact-form-wrapper">
                        <h2 class="contact-form-title">{{ t('contact.form.title') }}</h2>

                        <div v-if="flashSuccess" class="alert alert--success">
                            {{ flashSuccess }}
                        </div>

                        <form class="contact-form" @submit.prevent="submit" novalidate>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        {{ t('contact.form.name') }} *
                                    </label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="form-input"
                                        :class="{ 'form-input--error': form.errors.name }"
                                        required
                                        autocomplete="name"
                                    />
                                    <span v-if="form.errors.name" class="form-error">{{ form.errors.name }}</span>
                                </div>

                                <div class="form-group">
                                    <label for="mobile" class="form-label">
                                        {{ t('contact.form.mobile') }}
                                    </label>
                                    <input
                                        id="mobile"
                                        v-model="form.mobile"
                                        type="tel"
                                        dir="ltr"
                                        class="form-input"
                                        :class="{ 'form-input--error': form.errors.mobile }"
                                        autocomplete="tel"
                                    />
                                    <span v-if="form.errors.mobile" class="form-error">{{ form.errors.mobile }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">
                                    {{ t('contact.form.email') }} *
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="form-input"
                                    :class="{ 'form-input--error': form.errors.email }"
                                    required
                                    autocomplete="email"
                                />
                                <span v-if="form.errors.email" class="form-error">{{ form.errors.email }}</span>
                            </div>

                            <div class="form-group">
                                <label for="message" class="form-label">
                                    {{ t('contact.form.message') }} *
                                </label>
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    class="form-textarea"
                                    :class="{ 'form-textarea--error': form.errors.message }"
                                    rows="6"
                                    required
                                ></textarea>
                                <span v-if="form.errors.message" class="form-error">{{ form.errors.message }}</span>
                            </div>

                            <input
                                v-model="form.website"
                                type="text"
                                name="website"
                                class="honeypot"
                                tabindex="-1"
                                autocomplete="off"
                                aria-hidden="true"
                            />

                            <button
                                type="submit"
                                class="btn btn--primary btn--submit"
                                :class="{ 'btn--loading': form.processing }"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">{{ t('contact.form.sending') }}</span>
                                <span v-else>{{ t('contact.form.send') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
