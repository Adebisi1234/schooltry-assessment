<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { User } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

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
    content: string;
}

interface Props {
    document: Document;
    filePath: string;
}

interface Auth {
    user: User;
}
const props = defineProps<Props & { auth: Auth }>();
const isLoading = ref(true);
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// For PDF viewer
const showPdfViewer = ref(false);
const isPdfLoaded = ref(false);

onMounted(() => {
    isLoading.value = false;

    if (props.document.file_type === 'pdf') {
        showPdfViewer.value = true;
    }
});

const handlePdfLoad = () => {
    isPdfLoaded.value = true;
};
console.log({ document: props.document });
</script>

<template>
    <Head :title="document.title" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800">Document: {{ document.title }}</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6">
                        <!-- Document details -->
                        <div class="mb-6">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900">{{ document.title }}</h1>
                                    <p v-if="document.description" class="mt-2 text-gray-600">{{ document.description }}</p>

                                    <div class="mt-4 text-sm text-gray-500">
                                        <p>Uploaded by: {{ document.user.name }}</p>
                                        <p>Date: {{ formatDate(document.created_at) }}</p>
                                        <p v-if="document.file_size">Size: {{ formatFileSize(document.file_size) }}</p>
                                        <p>Type: {{ document.file_type.toUpperCase() }}</p>
                                    </div>
                                </div>

                                <div class="flex space-x-2">
                                    <a
                                        :href="filePath"
                                        target="_blank"
                                        class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:bg-blue-800"
                                    >
                                        Download
                                    </a>

                                    <Link
                                        v-if="props.auth.user.role === 'student'"
                                        :href="`/documents/${document.id}/chat`"
                                        class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-green-700 focus:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none active:bg-green-800"
                                    >
                                        Chat with AI
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Document content preview -->
                        <div class="mt-8 overflow-hidden rounded-lg border bg-gray-50">
                            <div class="border-b bg-gray-100 p-4">
                                <h3 class="font-medium">Document Preview</h3>
                            </div>

                            <div class="p-6">
                                <!-- Loading state -->
                                <div v-if="isLoading" class="flex items-center justify-center py-12">
                                    <svg
                                        class="h-8 w-8 animate-spin text-gray-500"
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
                                </div>

                                <!-- PDF Viewer -->
                                <div v-else-if="showPdfViewer" class="h-[70vh]">
                                    <div v-if="!isPdfLoaded" class="flex items-center justify-center py-12">
                                        <p class="text-gray-500">Loading PDF...</p>
                                    </div>
                                    <iframe :src="filePath" class="h-full w-full border-0" @load="handlePdfLoad"></iframe>
                                </div>

                                <!-- Text file viewer (simplified) -->
                                <div
                                    v-else-if="document.file_type === 'txt' || document.file_type === 'text/plain'"
                                    class="rounded border bg-white p-4"
                                >
                                    <p class="mt-4 text-sm whitespace-pre-wrap text-gray-500">
                                        {{ document.content }}
                                    </p>
                                </div>

                                <!-- Doc/Docx file viewer (simplified) -->
                                <div v-else-if="['doc', 'docx'].includes(document.file_type)" class="rounded border bg-white p-4">
                                    <p class="text-gray-800">Document preview not available for {{ document.file_type.toUpperCase() }} files.</p>
                                    <p class="mt-4 text-sm text-gray-500">Please download the file to view its contents.</p>
                                </div>

                                <!-- Fallback for other file types -->
                                <div v-else class="flex flex-col items-center justify-center py-12">
                                    <svg class="h-16 w-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                            clip-rule="evenodd"
                                        ></path>
                                    </svg>
                                    <p class="mt-4 text-gray-600">Preview not available for this file type.</p>
                                    <a :href="filePath" target="_blank" class="mt-4 text-blue-600 hover:text-blue-800">Download to view</a>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation buttons -->
                        <div class="mt-8 flex justify-between">
                            <Link
                                href="/documents"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-200 px-4 py-2 text-xs font-semibold tracking-widest text-gray-800 uppercase transition duration-150 ease-in-out hover:bg-gray-300 focus:bg-gray-300 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:outline-none active:bg-gray-400"
                            >
                                Back to Documents
                            </Link>

                            <div class="flex space-x-2">
                                <Link
                                    v-if="props.auth.user.id === document.user.id"
                                    :href="`/documents/${document.id}/edit`"
                                    class="inline-flex items-center rounded-md border border-transparent bg-yellow-500 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-yellow-600 focus:bg-yellow-600 focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 focus:outline-none active:bg-yellow-700"
                                >
                                    Edit
                                </Link>

                                <Link
                                    v-if="props.auth.user.id === document.user.id || props.auth.user.role === 'admin'"
                                    :href="`/documents/${document.id}`"
                                    method="delete"
                                    as="button"
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-red-700 focus:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none active:bg-red-800"
                                    @click.prevent="
                                        () => {
                                            $inertia.delete(`/documents/${document.id}`);
                                            $inertia.visit(route('documents.index'));
                                        }
                                    "
                                >
                                    Delete
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
