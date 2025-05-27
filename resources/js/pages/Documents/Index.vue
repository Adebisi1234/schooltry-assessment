<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

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

interface Props {
    documents: {
        data: Document[];
        links: any[];
    };
}

const form = useForm({});

const page = usePage<SharedData & Props>();
const getFileIcon = (fileType: string) => {
    switch (fileType) {
        case 'pdf':
            return '📄';
        case 'doc':
        case 'docx':
            return '📝';
        case 'txt':
            return '📃';
        default:
            return '📁';
    }
};

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Documents" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800">Documents</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6">
                        <div class="mb-6 flex justify-between">
                            <h3 class="text-lg font-medium text-gray-900">All Documents</h3>
                            <Link
                                v-if="page.props.auth.user?.role === 'teacher'"
                                href="/documents/create"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-gray-900"
                            >
                                Upload New Document
                            </Link>
                        </div>

                        <div v-if="page.props.documents?.data?.length === 0 || !page.props.documents?.data" class="py-8 text-center">
                            <p class="text-gray-500">No documents available yet.</p>
                            <div v-if="page.props.auth.user?.role === 'teacher'" class="mt-4">
                                <Link href="/documents/create" class="text-indigo-600 hover:text-indigo-900"> Upload your first document </Link>
                            </div>
                        </div>

                        <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="document in page.props.documents.data"
                                :key="document?.id"
                                class="overflow-hidden rounded-lg border shadow-sm transition-shadow hover:shadow-md"
                            >
                                <div class="border-b bg-gray-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-2xl">{{ getFileIcon(document?.file_type) }}</span>
                                        <span class="text-xs text-gray-500">{{ document?.file_type.toUpperCase() }}</span>
                                    </div>
                                </div>

                                <div class="p-4">
                                    <h4 class="truncate text-lg font-medium text-gray-900">{{ document?.title }}</h4>
                                    <p v-if="document?.description" class="mt-1 line-clamp-2 text-sm text-gray-600">{{ document?.description }}</p>

                                    <div class="mt-4 flex justify-between text-xs text-gray-500">
                                        <span>By: {{ document?.user?.name }}</span>
                                        <span>{{ formatDate(document?.created_at) }}</span>
                                    </div>

                                    <div v-if="document?.file_size" class="mt-1 text-xs text-gray-500">
                                        Size: {{ formatFileSize(document?.file_size) }}
                                    </div>

                                    <div class="mt-4 flex space-x-2">
                                        <Link
                                            :href="`/documents/${document?.id}`"
                                            class="inline-flex items-center rounded-md border border-transparent bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 hover:bg-blue-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                                        >
                                            View
                                        </Link>

                                        <Link
                                            v-if="page.props.auth.user?.role === 'student'"
                                            :href="`/documents/${document?.id}/chat`"
                                            class="inline-flex items-center rounded-md border border-transparent bg-green-100 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-200 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none"
                                        >
                                            AI Chat
                                        </Link>

                                        <Link
                                            v-if="page.props.auth.user?.id === document?.user?.id"
                                            :href="`/documents/${document?.id}/edit`"
                                            class="inline-flex items-center rounded-md border border-transparent bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700 hover:bg-yellow-200 focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 focus:outline-none"
                                        >
                                            Edit
                                        </Link>

                                        <Link
                                            v-if="page.props.auth.user?.id === document?.user?.id || page.props.auth.user?.role === 'admin'"
                                            :href="`/documents/${document?.id}`"
                                            method="delete"
                                            as="button"
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-transparent bg-red-100 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-200 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none"
                                            @click.prevent="form.delete(route(`/documents/${document?.id}`))"
                                        >
                                            Delete
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="page.props.documents.links.length > 3" class="mt-6">
                            <div class="-mb-1 flex flex-wrap">
                                <template v-for="(link, index) in page.props.documents.links" :key="index">
                                    <div
                                        v-if="link.url === null"
                                        class="mr-1 mb-1 rounded-md border px-4 py-2 text-sm text-gray-500"
                                        v-html="link.label"
                                    ></div>
                                    <Link
                                        v-else
                                        :href="link.url"
                                        class="mr-1 mb-1 rounded-md border px-4 py-2 text-sm"
                                        :class="{ 'bg-blue-500 text-white': link.active, 'text-gray-500 hover:bg-gray-100': !link.active }"
                                        v-html="link.label"
                                    ></Link>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
