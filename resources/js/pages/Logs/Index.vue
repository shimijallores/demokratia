<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Transmission Log',
                href: '/logs',
            },
        ],
    },
});

interface Batch {
    id: string;
    precinct_id: number;
    ballot_count: number;
    transmission_mode: string;
    checksum: string;
    received_at: string | null;
    status: string;
    created_at: string;
    precinct: {
        precinct_code: string;
        name: string;
    };
}

interface PaginatedData {
    data: Batch[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

const props = defineProps<{
    batches: PaginatedData;
}>();

const statusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        pending: 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        processing: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-300',
        complete: 'bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300',
        failed: 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300',
    };
    return classes[status] || classes.pending;
};

function handlePageChange(url: string | null) {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Transmission Log" />

    <div class="mx-auto w-full lg:w-[60%]">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="rounded-2xl ring-1 ring-border">
                <CardHeader>
                    <CardTitle>Transmission Log</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow class="h-12">
                                <TableHead class="px-4">Timestamp</TableHead>
                                <TableHead class="px-4">Precinct</TableHead>
                                <TableHead class="px-4">Batch ID</TableHead>
                                <TableHead class="px-4">Ballots</TableHead>
                                <TableHead class="px-4">Mode</TableHead>
                                <TableHead class="px-4">Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="batch in batches.data" :key="batch.id">
                                <TableCell class="px-4 py-4">{{ new Date(batch.created_at).toLocaleString() }}</TableCell>
                                <TableCell class="px-4 py-4">{{ batch.precinct.precinct_code }}</TableCell>
                                <TableCell class="px-4 py-4 font-mono text-xs">{{ batch.id }}</TableCell>
                                <TableCell class="px-4 py-4">{{ batch.ballot_count }}</TableCell>
                                <TableCell class="px-4 py-4">
                                    <Badge variant="outline">{{ batch.transmission_mode }}</Badge>
                                </TableCell>
                                <TableCell class="px-4 py-4">
                                    <Badge :class="statusBadgeClass(batch.status)">{{ batch.status }}</Badge>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="batches.data.length === 0">
                                <TableCell colspan="6" class="text-center">No transmissions yet.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ batches.total }} transmission(s)
                        </p>
                        <div class="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!batches.prev_page_url"
                                @click="handlePageChange(batches.prev_page_url)"
                            >
                                Previous
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="!batches.next_page_url"
                                @click="handlePageChange(batches.next_page_url)"
                            >
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
