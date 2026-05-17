<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Export',
                href: '/export',
            },
        ],
    },
});

const props = defineProps<{
    totalVotes: number;
    totalBatches: number;
}>();

function downloadCsv() {
    window.location.href = '/export/csv';
}

function downloadJson() {
    window.location.href = '/export/json';
}
</script>

<template>
    <Head title="Export" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <Card class="max-w-lg rounded-2xl ring-1 ring-border">
            <CardHeader>
                <CardTitle>Export Results</CardTitle>
                <CardDescription
                    >Download election results in your preferred
                    format.</CardDescription
                >
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="rounded-lg bg-muted p-4">
                    <p class="text-sm text-muted-foreground">
                        Total Votes:
                        <span class="font-medium text-foreground">{{
                            totalVotes.toLocaleString()
                        }}</span>
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Batches Processed:
                        <span class="font-medium text-foreground">{{
                            totalBatches
                        }}</span>
                    </p>
                </div>

                <div class="flex gap-4">
                    <Button @click="downloadCsv" class="flex-1">
                        Download CSV
                    </Button>
                    <Button
                        variant="outline"
                        @click="downloadJson"
                        class="flex-1"
                    >
                        Download JSON
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
