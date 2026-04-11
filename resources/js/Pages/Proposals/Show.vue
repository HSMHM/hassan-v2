<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
    meta: Object,
    proposal: Object,
});

const page = usePage();
const locale = computed(() => page.props.locale);
const content = computed(() => props.proposal.content || {});
</script>

<template>
    <MainLayout :meta="meta">
        <section class="section">
            <div class="container">
                <article class="proposal">
                    <header class="proposal__header">
                        <h1 class="proposal__customer">{{ proposal.customer_name }}</h1>
                        <p class="proposal__id">
                            {{ locale === 'ar' ? 'رقم العرض:' : 'Proposal ID:' }} {{ proposal.proposal_id }}
                        </p>
                        <p v-if="proposal.description">{{ proposal.description }}</p>
                    </header>

                    <section v-if="content.introduction" class="proposal__section">
                        <h2 class="proposal__section-title">{{ content.introduction.title }}</h2>
                        <p v-for="(line, idx) in content.introduction.content || []" :key="idx">{{ line }}</p>
                    </section>

                    <section v-if="content.items?.length" class="proposal__section">
                        <h2 class="proposal__section-title">{{ content.itemsTableTitle }}</h2>
                        <table class="proposal__table">
                            <thead>
                                <tr>
                                    <th>{{ locale === 'ar' ? 'البند' : 'Item' }}</th>
                                    <th>{{ locale === 'ar' ? 'الوصف' : 'Description' }}</th>
                                    <th>{{ locale === 'ar' ? 'الوحدة' : 'Unit' }}</th>
                                    <th>{{ locale === 'ar' ? 'القيمة' : 'Value' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, idx) in content.items" :key="idx">
                                    <td>{{ item.item }}</td>
                                    <td>{{ item.description }}</td>
                                    <td>{{ item.unit }}</td>
                                    <td>{{ item.value }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </section>

                    <section v-if="content.itemsOverview?.length" class="proposal__section">
                        <h2 class="proposal__section-title">{{ locale === 'ar' ? 'تفاصيل البنود' : 'Item Details' }}</h2>
                        <div v-for="(group, idx) in content.itemsOverview" :key="idx">
                            <h3>{{ group.title }}</h3>
                            <ul>
                                <li v-for="(d, i) in group.details || []" :key="i">{{ d }}</li>
                            </ul>
                        </div>
                    </section>

                    <section v-if="content.notes?.length" class="proposal__section">
                        <h2 class="proposal__section-title">{{ content.notesTitle || (locale === 'ar' ? 'ملاحظات' : 'Notes') }}</h2>
                        <ol>
                            <li v-for="(note, idx) in content.notes" :key="idx">
                                <p>{{ note.text }}</p>
                                <ul v-if="note.subNotes?.length">
                                    <li v-for="(sn, i) in note.subNotes" :key="i">{{ sn }}</li>
                                </ul>
                                <p v-else-if="note.subNote"><em>{{ note.subNote }}</em></p>
                            </li>
                        </ol>
                    </section>
                </article>
            </div>
        </section>
    </MainLayout>
</template>
