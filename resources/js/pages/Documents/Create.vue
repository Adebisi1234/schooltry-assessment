<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    title: '',
    description: '',
    document: null as File | null,
});

const fileInput = ref<HTMLInputElement | null>(null);
const fileName = ref<string>('');
const fileSize = ref<number>(0);
const isUploading = ref<boolean>(false);

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0] || null;
    form.document = file;
    fileName.value = file?.name || '';
    fileSize.value = file?.size || 0;
};

const submit = () => {
    isUploading.value = true;
    form.post('/documents', {
        preserveScroll: true,
        onFinish: () => {
            isUploading.value = false;
        },
    });
};

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head title="Upload Document" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800">Upload Document</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6">
                        <h3 class="mb-6 text-lg font-medium text-gray-900">New Document</h3>

                        <form @submit.prevent="submit">
                            <div class="mb-6">
                                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    class="focus:ring-opacity-50 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                    required
                                />
                                <div v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</div>
                            </div>

                            <div class="mb-6">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="focus:ring-opacity-50 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                    rows="3"
                                ></textarea>
                                <div v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</div>
                            </div>

                            <div class="mb-6">
                                <label for="document" class="block text-sm font-medium text-gray-700">Document</label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" ref="fileInput" @change="handleFileChange" class="hidden" accept=".pdf,.doc,.docx,.txt" />
                                    <button
                                        type="button"
                                        @click="fileInput?.click()"
                                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-25"
                                    >
                                        Choose File
                                    </button>
                                    <span v-if="fileName" class="ml-3 text-sm text-gray-600"> {{ fileName }} ({{ formatFileSize(fileSize) }}) </span>
                                    <span v-else class="ml-3 text-sm text-gray-500">No file chosen</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Accepted file types: PDF, DOC, DOCX, TXT (max 10MB)</p>
                                <div v-if="form.errors.document" class="mt-1 text-sm text-red-500">{{ form.errors.document }}</div>
                            </div>

                            <div class="mt-6 flex items-center justify-end">
                                <a
                                    href="/documents"
                                    class="mr-2 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-25"
                                >
                                    Cancel
                                </a>
                                <button
                                    type="submit"
                                    class="ml-4 inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-gray-900"
                                    :disabled="isUploading || form.processing"
                                >
                                    <svg
                                        v-if="isUploading || form.processing"
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
                                    {{ isUploading ? 'Uploading...' : 'Upload Document' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
