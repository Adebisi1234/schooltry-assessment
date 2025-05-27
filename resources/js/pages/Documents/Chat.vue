<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { nextTick, onMounted, ref } from 'vue';

interface Document {
    id: number;
    title: string;
    description: string;
    file_path: string;
    file_type: string;
    file_size: number;
    created_at: string;
    user: {
        id: number;
        name: string;
    };
}

interface Message {
    id: string;
    role: 'user' | 'assistant';
    content: string;
    timestamp: Date;
}

interface Props {
    document: Document;
}

const props = defineProps<Props>();
const messages = ref<Message[]>([
    {
        id: '0',
        role: 'assistant',
        content: `Hi there! I'm your AI assistant for "${props.document.title}". How can I help you understand this document better?`,
        timestamp: new Date(),
    },
]);

const newMessage = ref('');
const isLoading = ref(false);
const chatContainer = ref<HTMLElement | null>(null);

const sendMessage = async () => {
    if (!newMessage.value.trim() || isLoading.value) return;

    const userMessage: Message = {
        id: Date.now().toString(),
        role: 'user',
        content: newMessage.value,
        timestamp: new Date(),
    };

    messages.value.push(userMessage);
    const question = newMessage.value;
    newMessage.value = '';

    // Scroll to bottom after user message is added
    await nextTick();
    scrollToBottom();

    isLoading.value = true;

    try {
        // Add temporary loading message
        const loadingId = Date.now().toString();
        messages.value.push({
            id: loadingId,
            role: 'assistant',
            content: '...',
            timestamp: new Date(),
        });

        // Send question to the server
        const response = await axios.post(`/documents/${props.document.id}/ask`, {
            question: question,
        });

        // Replace loading message with actual response
        const loadingIndex = messages.value.findIndex((m) => m.id === loadingId);
        if (loadingIndex !== -1) {
            messages.value[loadingIndex] = {
                id: loadingId,
                role: 'assistant',
                content: response.data.answer,
                timestamp: new Date(),
            };
        }
    } catch (error) {
        console.error('Error getting answer:', error);

        // Replace loading message with error
        const loadingIndex = messages.value.findIndex((m) => m.content === '...');
        if (loadingIndex !== -1) {
            messages.value[loadingIndex] = {
                id: Date.now().toString(),
                role: 'assistant',
                content: 'Sorry, I encountered an error while processing your question. Please try again.',
                timestamp: new Date(),
            };
        }
    } finally {
        isLoading.value = false;

        // Scroll to bottom after response is received
        await nextTick();
        scrollToBottom();
    }
};

const scrollToBottom = () => {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

onMounted(() => {
    scrollToBottom();
});

const formatTime = (date: Date) => {
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`AI Chat: ${document.title}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800">AI Chat: {{ document.title }}</h2>
                <Link :href="`/documents/${document.id}`" class="text-sm text-blue-600 hover:text-blue-800"> Back to Document </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="flex h-[70vh] flex-col overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <!-- Chat header -->
                    <div class="flex items-center border-b bg-gray-50 p-4">
                        <div class="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                <path
                                    fill-rule="evenodd"
                                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium">Document Assistant</h3>
                            <p class="text-xs text-gray-500">Ask questions about "{{ document.title }}"</p>
                        </div>
                    </div>

                    <!-- Chat messages -->
                    <div ref="chatContainer" class="flex-1 space-y-4 overflow-y-auto p-4">
                        <div v-for="message in messages" :key="message.id" class="flex" :class="{ 'justify-end': message.role === 'user' }">
                            <div
                                class="max-w-3/4"
                                :class="{
                                    'rounded-lg bg-blue-100 p-3': message.role === 'user',
                                    'rounded-lg bg-gray-100 p-3': message.role === 'assistant' && message.content !== '...',
                                    'flex items-center space-x-1': message.content === '...',
                                }"
                            >
                                <!-- Loading animation -->
                                <template v-if="message.content === '...'">
                                    <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400" style="animation-delay: 0ms"></div>
                                    <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400" style="animation-delay: 100ms"></div>
                                    <div class="h-2 w-2 animate-bounce rounded-full bg-gray-400" style="animation-delay: 200ms"></div>
                                </template>

                                <!-- Regular message content -->
                                <template v-else>
                                    <p class="text-gray-800">{{ message.content }}</p>
                                    <p class="mt-1 text-right text-xs text-gray-500">{{ formatTime(message.timestamp) }}</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Chat input -->
                    <div class="border-t p-4">
                        <form @submit.prevent="sendMessage" class="flex space-x-2">
                            <input
                                v-model="newMessage"
                                type="text"
                                placeholder="Ask a question about this document..."
                                class="focus:ring-opacity-50 flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                :disabled="isLoading"
                            />
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:bg-blue-800"
                                :disabled="!newMessage.trim() || isLoading"
                            >
                                <svg
                                    v-if="isLoading"
                                    class="mr-2 -ml-1 h-4 w-4 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                <span>Send</span>
                            </button>
                        </form>
                        <p class="mt-2 text-xs text-gray-500">
                            This AI assistant can answer questions about the document content. For best results, ask specific questions.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
