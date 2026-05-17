<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: '/dashboard',
            },
        ],
    },
});

const props = defineProps<{
    precinctCounts: Record<string, number>;
    reportingProgress: {
        total: number;
        complete: number;
        transmitting: number;
        partial: number;
        percentage: number;
    };
    totalBatches: number;
    completeBatches: number;
    totalVotes: number;
    activeSessions: number;
    queueDepth: number;
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="mx-auto w-full lg:w-[60%]">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <Card class="rounded-2xl ring-1 ring-border">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Total Precincts</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ reportingProgress.total }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ reportingProgress.complete }} reporting
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-2xl ring-1 ring-border">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Total Votes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ totalVotes.toLocaleString() }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ completeBatches }} batches processed
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-2xl ring-1 ring-border">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Active Sessions</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ activeSessions }}</div>
                        <p class="text-xs text-muted-foreground">
                            Upload sessions
                        </p>
                    </CardContent>
                </Card>

                <Card class="rounded-2xl ring-1 ring-border">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Queue Depth</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ queueDepth }}</div>
                        <p class="text-xs text-muted-foreground">
                            Pending jobs
                        </p>
                    </CardContent>
                </Card>
            </div>

            <Card class="rounded-2xl ring-1 ring-border">
                <CardHeader>
                    <CardTitle>Reporting Progress</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">
                            {{ reportingProgress.complete }} of {{ reportingProgress.total }} precincts reporting
                        </span>
                        <span class="text-sm font-medium">{{ reportingProgress.percentage }}%</span>
                    </div>
                    <Progress :model-value="reportingProgress.percentage" class="h-2" />

                    <div class="mt-4 flex gap-4">
                        <Badge variant="outline" class="bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300">
                            Complete: {{ precinctCounts.complete || 0 }}
                        </Badge>
                        <Badge variant="outline" class="bg-yellow-50 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-300">
                            Transmitting: {{ precinctCounts.transmitting || 0 }}
                        </Badge>
                        <Badge variant="outline" class="bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                            Partial: {{ precinctCounts.partial || 0 }}
                        </Badge>
                        <Badge variant="outline" class="bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            Pending: {{ precinctCounts.pending || 0 }}
                        </Badge>
                        <Badge variant="outline" class="bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300">
                            Error: {{ precinctCounts.error || 0 }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
