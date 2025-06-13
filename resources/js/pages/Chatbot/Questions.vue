<template>
    <Head title="Chatbot Questions" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800">Chatbot Questions</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium">Frequently Asked Questions</h3>
                            <p class="text-sm text-gray-600">Questions are ranked by frequency of being asked.</p>
                        </div>

                        <div v-if="loading" class="my-8 flex justify-center">
                            <div class="h-10 w-10 animate-spin rounded-full border-b-2 border-blue-600"></div>
                        </div>

                        <div v-else-if="questions.length === 0" class="py-8 text-center text-gray-500">No questions have been asked yet.</div>

                        <div v-else>
                            <div class="grid grid-cols-1 gap-4">
                                <div
                                    v-for="(item, index) in questions"
                                    :key="index"
                                    class="rounded-lg border p-4 transition-colors duration-150 hover:bg-gray-50"
                                >
                                    <div class="flex items-start">
                                        <div class="mr-4 flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-800">
                                            {{ index + 1 }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="mb-2">
                                                <h4 class="text-lg font-semibold">{{ item.question }}</h4>
                                                <div class="mt-1 flex items-center text-sm text-gray-500">
                                                    <span class="mr-3">
                                                        <span class="font-medium text-blue-600">{{ item.frequency }}</span> times asked
                                                    </span>
                                                    <span>Document: {{ item.document_title }}</span>
                                                </div>
                                            </div>
                                            <div class="mt-2 rounded-lg bg-gray-50 p-3">
                                                <p class="text-gray-800">{{ item.answer }}</p>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500">
                                                First asked: {{ formatDate(item.created_at) }}
                                                <span v-if="item.last_accessed" class="ml-3">
                                                    Last accessed: {{ formatDate(item.last_accessed) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

interface QuestionData {
    question: string;
    answer: string;
    document_id: number;
    document_title: string;
    created_at: string;
    last_accessed: string;
    frequency: number;
}

const questions = ref<QuestionData[]>([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const response = await axios.get('/chatbot/questions');
        questions.value = response.data.questions;
    } catch (error) {
        console.error('Error fetching questions:', error);
    } finally {
        loading.value = false;
    }
});

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
